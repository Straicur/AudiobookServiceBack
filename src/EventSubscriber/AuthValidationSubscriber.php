<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Annotation\AuthValidation;
use App\Exception\DataNotFoundException;
use App\Service\AuthorizedUserService;
use App\Service\AuthValidationService;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthValidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $requestLogger,
        private readonly AuthValidationService $authValidationService,
    ) {
    }

    public function onControllerCall(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (is_array($controller)) {
            $method = $controller[1];
            $controller = current($controller);

            try {
                $controllerReflectionClass = new ReflectionClass($controller);
                $reflectionMethod = $controllerReflectionClass->getMethod($method);
                $methodAttributes = $reflectionMethod->getAttributes(AuthValidation::class);

                if (count($methodAttributes) === 1) {
                    $authValidationAttribute = current($methodAttributes)->newInstance();

                    if ($authValidationAttribute instanceof AuthValidation && $authValidationAttribute->isCheckAuthToken()) {
                        $authToken = $this->authValidationService->getAuthenticatedUserToken($request);

                        $loggedUserData = [
                            'method'        => $reflectionMethod->class . '::' . $reflectionMethod->name,
                            'user_id'       => $authToken->getUser()->getId(),
                            'token_auth_id' => $authToken->getId(),
                            'user_data'     => [
                                'email' => $authToken->getUser()->getUserInformation()->getEmail(),
                            ],
                        ];

                        $this->requestLogger->info('Logged user action', $loggedUserData);

                        $user = $authToken->getUser();

                        $this->authValidationService->checkIfUserIsDeleted($user);
                        $this->authValidationService->checkIfUserHasRoles($user, $authValidationAttribute);
                        $this->authValidationService->checkTechnicalBreak($user);
                        $this->authValidationService->checkIfUserIsBanned($user, $authToken);

                        $this->authValidationService->addAuthTokenTime($authToken);

                        AuthorizedUserService::setAuthenticationToken($authToken);
                        AuthorizedUserService::setAuthorizedUser($authToken->getUser());
                    }
                }
            } catch (ReflectionException | NonUniqueResultException | InvalidArgumentException) {
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
}
