<?php

namespace App\EventSubscriber;

use App\Annotation\AuthValidation;
use App\Entity\User;
use App\Exception\AuthenticationException;
use App\Exception\DataNotFoundException;
use App\Exception\PermissionException;
use App\Exception\TechnicalBreakException;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\TechnicalBreakRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserService;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
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
    private LoggerInterface $requestLogger;

    public function __construct(
        AuthenticationTokenRepository $authenticationTokenRepository,
        TechnicalBreakRepository      $technicalBreakRepository,
        UserRepository                $userRepository,
        LoggerInterface               $requestLogger,
    )
    {
        $this->authenticationTokenRepository = $authenticationTokenRepository;
        $this->technicalBreakRepository = $technicalBreakRepository;
        $this->userRepository = $userRepository;
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

                if (count($methodAttributes) === 1) {
                    $authValidationAttribute = $methodAttributes[0]->newInstance();

                    if (($authValidationAttribute instanceof AuthValidation) && $authValidationAttribute->isCheckAuthToken()) {
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

                        //TODO dodaj sprawdzenie w cache i dopiero kiedy nie ma go tam to dodaję
                        // Plus zamień resztę wtedy takich danych żeby to się cachowało
                        // Tu będzie ewidentnie za dużo szukania

                        $technicalBreak = $this->technicalBreakRepository->findOneBy([
                            "active" => true
                        ]);

                        if (($_ENV["APP_ENV"] !== "test") && $technicalBreak !== null && !$authToken->getUser()->getUserSettings()->isAdmin()) {
                            throw new TechnicalBreakException();
                        }

                        $foundUserRole = $this->checkRoles($authToken->getUser(), $authValidationAttribute->getRoles());

                        if (!$foundUserRole) {
                            throw new PermissionException();
                        }
                    }
                }

            } catch (\ReflectionException|NonUniqueResultException) {
                throw new DataNotFoundException();
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onControllerCall',
        ];
    }

    /**
     * @param User $user
     * @param string[] $roles
     * @return int
     * @throws PermissionException
     */
    private function checkRoles(User $user, array $roles): int
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

        return $foundRole;
    }
}
