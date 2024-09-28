<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Annotation\AuthValidation;
use App\Entity\User;
use App\Enums\Cache\AdminCacheKeys;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserCacheKeys;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\AuthenticationException;
use App\Exception\DataNotFoundException;
use App\Exception\PermissionException;
use App\Exception\TechnicalBreakException;
use App\Exception\UserDeletedException;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\TechnicalBreakRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
use App\Service\AuthorizedUserService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AuthValidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthenticationTokenRepository $authenticationTokenRepository,
        private readonly TechnicalBreakRepository $technicalBreakRepository,
        private readonly UserRepository $userRepository,
        private readonly UserDeleteRepository $deleteRepository,
        private readonly LoggerInterface $requestLogger,
        private readonly TagAwareCacheInterface $stockCache,
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

                    if (($authValidationAttribute instanceof AuthValidation) && $authValidationAttribute->isCheckAuthToken()) {
                        $authorizationHeaderField = $request->headers->get('authorization');

                        if ($authorizationHeaderField === null) {
                            throw new AuthenticationException();
                        }

                        $authToken = $this->authenticationTokenRepository->findActiveToken($authorizationHeaderField);

                        if ($authToken === null) {
                            throw new AuthenticationException();
                        }

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

                        $userDeleted = $this->stockCache->get(UserCacheKeys::USER_DELETED->value . $user->getId(), function (ItemInterface $item) use ($user) {
                            $item->expiresAfter(CacheValidTime::DAY->value);
                            $item->tag(UserStockCacheTags::USER_DELETED->value);

                            return $this->deleteRepository->findOneBy([
                                'user'    => $user->getId(),
                                'deleted' => true,
                            ]);
                        });

                        if ($userDeleted !== null) {
                            throw new UserDeletedException();
                        }

                        $technicalBreak = $this->stockCache->get(AdminCacheKeys::ADMIN_TECHNICAL_BREAK->value, function (ItemInterface $item) {
                            $item->expiresAfter(CacheValidTime::DAY->value);
                            $item->tag(AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value);

                            return $this->technicalBreakRepository->findOneBy([
                                'active' => true,
                            ]);
                        });

                        if (($_ENV['APP_ENV'] !== 'test') && $technicalBreak !== null && !$authToken->getUser()->getUserSettings()->isAdmin()) {
                            throw new TechnicalBreakException();
                        }

                        $foundUserRole = $this->checkRoles($authToken->getUser(), $authValidationAttribute->getRoles());

                        if (!$foundUserRole) {
                            throw new PermissionException();
                        }

                        if ($user->isBanned()) {
                            if ($user->getBannedTo() < new DateTime()) {
                                $user->setBanned(false);
                                $this->userRepository->add($user);
                            } else {
                                $authToken->setDateExpired(new DateTime());
                                $this->authenticationTokenRepository->add($authToken);

                                throw new PermissionException();
                            }
                        }

                        $dateNew = clone $authToken->getDateExpired();
                        $dateNew->modify('+2 second');
                        $authToken->setDateExpired($dateNew);

                        $this->authenticationTokenRepository->add($authToken);

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

    /**
     * @param UserRolesNames[] $roles
     */
    private function checkRoles(User $user, array $roles): bool
    {
        $userRoles = $user->getRoles();

        $foundRole = false;

        foreach ($userRoles as $userRole) {
            foreach ($roles as $role) {
                if ($userRole->getName() === $role->value) {
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
