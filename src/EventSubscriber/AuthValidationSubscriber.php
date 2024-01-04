<?php

namespace App\EventSubscriber;

use App\Annotation\AuthValidation;
use App\Entity\User;
use App\Exception\AuthenticationException;
use App\Exception\DataNotFoundException;
use App\Exception\PermissionException;
use App\Exception\ResponseExceptionInterface;
use App\Exception\TechnicalBreakException;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\TechnicalBreakRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserService;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * AuthValidationSubscriber
 *
 */
class AuthValidationSubscriber implements EventSubscriberInterface
{
    private AuthenticationTokenRepository $authenticationTokenRepository;
    private TechnicalBreakRepository $technicalBreakRepository;
    private UserRepository $userRepository;
    private LoggerInterface $responseLogger;
    private LoggerInterface $requestLogger;

    public function __construct(
        AuthenticationTokenRepository $authenticationTokenRepository,
        TechnicalBreakRepository      $technicalBreakRepository,
        UserRepository                $userRepository,
        LoggerInterface               $responseLogger,
        LoggerInterface               $requestLogger,
    )
    {
        $this->authenticationTokenRepository = $authenticationTokenRepository;
        $this->technicalBreakRepository = $technicalBreakRepository;
        $this->userRepository = $userRepository;
        $this->responseLogger = $responseLogger;
        $this->requestLogger = $requestLogger;
    }

    /**
     * @param ControllerEvent $event
     * @return void
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws TechnicalBreakException
     * @throws DataNotFoundException
     */
    public function onControllerCall(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller)) {
            $method = $controller[1];
            $controller = $controller[0];

            try {
                $controllerReflectionClass = new \ReflectionClass($controller);
                $reflectionMethod = $controllerReflectionClass->getMethod($method);
                $methodAttributes = $reflectionMethod->getAttributes(AuthValidation::class);
                //TODO dodaj sprawdzenie w chache i dopiero kiedy nie ma go tam to dodaję
                $technicalBreak = $this->technicalBreakRepository->findOneBy([
                    "active" => true
                ]);

                if ($technicalBreak !== null) {
                    throw new TechnicalBreakException();
                }
                if (count($methodAttributes) === 1) {
                    $authValidationAttribute = $methodAttributes[0]->newInstance();

                    if ($authValidationAttribute instanceof AuthValidation) {
                        if ($authValidationAttribute->isCheckAuthToken()) {
                            $authorizationHeaderField = $request->headers->get("authorization");

                            if ($authorizationHeaderField === null) {
                                throw new AuthenticationException();
                            }

                            $authToken = $this->authenticationTokenRepository->findActiveToken($authorizationHeaderField);

                            if ($authToken === null) {
                                throw new AuthenticationException();
                            }

                            $loggedUserData = [
                                "method" => $reflectionMethod->class . "::" . $reflectionMethod->name,
                                "user_id" => $authToken->getUser()->getId(),
                                "token_auth_id" => $authToken->getId(),
                                "user_data" => [
                                    "email" => $authToken->getUser()->getUserInformation()->getEmail(),
                                ]
                            ];

                            $this->requestLogger->info("Logged user action", $loggedUserData);

                            $user = $authToken->getUser();

                            if ($user->isBanned()) {
                                if ($user->getBannedTo() < new \DateTime('Now')) {
                                    $user->setBanned(false);
                                    $this->userRepository->add($user);
                                } else {
                                    $authToken->setDateExpired(new \DateTime("now"));
                                    $this->authenticationTokenRepository->add($authToken);

                                    throw new PermissionException();
                                }
                            }

                            $dateNew = clone $authToken->getDateExpired();
                            $dateNew->modify("+2 second");
                            $authToken->setDateExpired($dateNew);

                            $this->authenticationTokenRepository->add($authToken);

                            AuthorizedUserService::setAuthenticationToken($authToken);
                            AuthorizedUserService::setAuthorizedUser($authToken->getUser());

                            $this->checkRoles($authToken->getUser(), $authValidationAttribute->getRoles());
                        }
                    }
                }

            } catch (\ReflectionException|NonUniqueResultException) {
                throw new DataNotFoundException();
            }
        }
    }

    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ResponseExceptionInterface) {
            $loggingContext = [
                "statusCode" => $exception->getResponse()->getStatusCode(),
                "file" => "[" . $exception->getLine() . "](" . $exception->getFile() . ")",
                "responseData" => json_decode($exception->getResponse()->getContent(), true)
            ];

            $this->responseLogger->info("ResponseException", $loggingContext);

            $event->setResponse($exception->getResponse());
        } else {
            $this->responseLogger->critical("ResponseException", ["class" => $exception::class, "data" => $exception]);

            switch ($exception::class) {
                case NotFoundHttpException::class:
                {
                    $notFoundException = new DataNotFoundException([$exception->getMessage()]);

                    $event->setResponse($notFoundException->getResponse());
                    break;
                }
            }
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $authorizationHeaderField = $request->headers->get("authorization");

        $authToken = null;
        if ($authorizationHeaderField != null) {
            $authToken = $this->authenticationTokenRepository->findActiveToken($authorizationHeaderField);
        }

        $headersIterator = $response->headers->getIterator();

        $loggerData = [
            "requestUrl" => $request->getUri(),
            "requestMethod" => $request->getMethod(),
            "user" => $authToken?->getUser()->getId(),
            "statusCode" => $response->getStatusCode(),
            "headers" => $headersIterator->getArrayCopy(),
            "responseData" => $response->getStatusCode() > 299 ? json_decode($response->getContent(), true) : null,
        ];

        if ($response->getStatusCode() > 499) {
            $this->responseLogger->error("Response data", $loggerData);
        } else {
            $this->responseLogger->info("Response data", $loggerData);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onControllerCall',
            KernelEvents::EXCEPTION => 'onKernelException',
            KernelEvents::RESPONSE => 'onKernelResponse'
        ];
    }

    /**
     * @param User $user
     * @param string[] $roles
     * @return void
     * @throws PermissionException
     */
    private function checkRoles(User $user, array $roles): void
    {
        $userRoles = $user->getRoles();

        $foundRole = false;

        foreach ($userRoles as $userRole) {
            foreach ($roles as $role) {
                if ($userRole->getName() === $role) {
                    $foundRole = true;
                    break;
                }
            }

            if ($foundRole) {
                break;
            }
        }

        if (!$foundRole) {
            throw new PermissionException();
        }
    }
}
