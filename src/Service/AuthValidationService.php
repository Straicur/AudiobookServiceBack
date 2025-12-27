<?php

declare(strict_types = 1);

namespace App\Service;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Entity\User;
use App\Enums\Cache\AdminCacheKeys;
use App\Enums\Cache\AdminStockCacheTags;
use App\Enums\Cache\CacheValidTime;
use App\Enums\Cache\UserCacheKeys;
use App\Enums\Cache\UserStockCacheTags;
use App\Enums\UserRolesNames;
use App\Exception\AuthenticationException;
use App\Exception\PermissionException;
use App\Exception\TechnicalBreakException;
use App\Exception\UserDeletedException;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\TechnicalBreakRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AuthValidationService implements AuthValidationServiceInterface
{
    public function __construct(
        private readonly AuthenticationTokenRepository $authenticationTokenRepository,
        private readonly TechnicalBreakRepository $technicalBreakRepository,
        private readonly UserRepository $userRepository,
        private readonly UserDeleteRepository $deleteRepository,
        private readonly TagAwareCacheInterface $stockCache,
        #[Autowire(env: 'bool:SEND_EMAIL')] private readonly bool $sendEmail,
    ) {}

    public function getAuthenticatedUserToken(Request $request): AuthenticationToken
    {
        $authorizationHeaderField = $request->headers->get('authorization');

        if (null === $authorizationHeaderField) {
            throw new AuthenticationException();
        }

        $authToken = $this->authenticationTokenRepository->findActiveToken($authorizationHeaderField);

        if (null === $authToken) {
            throw new AuthenticationException();
        }

        return $authToken;
    }

    public function checkIfUserIsDeleted(User $user): void
    {
        $userDeleted = $this->stockCache->get(UserCacheKeys::USER_DELETED->value . $user->getId(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(UserStockCacheTags::USER_DELETED->value);

            return $this->deleteRepository->findOneBy([
                'user'    => $user->getId(),
                'deleted' => true,
            ]);
        });

        if (null !== $userDeleted) {
            throw new UserDeletedException();
        }
    }

    public function checkTechnicalBreak(User $user): void
    {
        $technicalBreak = $this->stockCache->get(AdminCacheKeys::ADMIN_TECHNICAL_BREAK->value, function (ItemInterface $item) {
            $item->expiresAfter(CacheValidTime::DAY->value);
            $item->tag(AdminStockCacheTags::ADMIN_TECHNICAL_BREAK->value);

            return $this->technicalBreakRepository->findOneBy([
                'active' => true,
            ]);
        });

        if (true === $this->sendEmail && null !== $technicalBreak && !$user->getUserSettings()->isAdmin()) {
            throw new TechnicalBreakException();
        }
    }

    public function checkIfUserIsBanned(User $user, AuthenticationToken $authToken): void
    {
        if ($user->isBanned()) {
            if ($user->getBannedTo() < new DateTime()) {
                $this->unbanUser($user);
            } else {
                $authToken->setDateExpired(new DateTime());
                $this->authenticationTokenRepository->add($authToken);

                throw new PermissionException();
            }
        }
    }

    private function unbanUser(User $user): void
    {
        $user->setBanned(false);
        $this->userRepository->add($user);
    }

    public function addAuthTokenTime(AuthenticationToken $authToken): void
    {
        $dateNew = clone $authToken->getDateExpired();
        $dateNew->modify('+3 second');

        $authToken->setDateExpired($dateNew);

        $this->authenticationTokenRepository->add($authToken);
    }

    public function checkIfUserHasRoles(User $user, AuthValidation $authValidationAttribute): void
    {
        $foundUserRole = $this->checkRoles($user, $authValidationAttribute->getRoles());

        if (!$foundUserRole) {
            throw new PermissionException();
        }
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
