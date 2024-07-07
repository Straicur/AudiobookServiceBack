<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AuthenticationToken;
use App\Entity\User;
use App\Exception\AuthenticationException;
use App\Repository\AuthenticationTokenRepository;
use DateTime;

class AuthorizedUserService implements AuthorizedUserServiceInterface
{
    private static AuthenticationTokenRepository $authenticationTokenRepository;

    private static ?User $authorizedUser = null;

    private static ?AuthenticationToken $authenticationToken = null;

    public function __construct(AuthenticationTokenRepository $authenticationTokenRepository)
    {
        self::$authenticationTokenRepository = $authenticationTokenRepository;
    }

    public static function setAuthorizedUser(User $user): void
    {
        self::$authorizedUser = $user;
    }

    public static function setAuthenticationToken(?AuthenticationToken $authenticationToken): void
    {
        self::$authenticationToken = $authenticationToken;
    }

    public static function getAuthorizedUser(): User
    {
        if (self::$authorizedUser === null) {
            throw new AuthenticationException();
        }

        return self::$authorizedUser;
    }

    public static function unAuthorizeUser(): void
    {
        if (self::$authenticationToken !== null) {
            self::$authenticationToken->setDateExpired(new DateTime());
            self::$authenticationTokenRepository->add(self::$authenticationToken);
        }
    }
}