<?php

namespace App\Service;

use App\Entity\AuthenticationToken;
use App\Entity\User;
use App\Exception\AuthenticationException;
use App\Repository\AuthenticationTokenRepository;

/**
 * AuthorizedUserService
 *
 */
class AuthorizedUserService implements AuthorizedUserServiceInterface
{
    private static AuthenticationTokenRepository $authenticationTokenRepository;

    private static ?User $authorizedUser = null;

    private static ?AuthenticationToken $authenticationToken = null;

    public function __construct(AuthenticationTokenRepository $authenticationTokenRepository)
    {
        self::$authenticationTokenRepository = $authenticationTokenRepository;
    }

    public static function setAuthorizedUser(User $user){
        self::$authorizedUser = $user;
    }

    /**
     * @param AuthenticationToken|null $authenticationToken
     */
    public static function setAuthenticationToken(?AuthenticationToken $authenticationToken): void
    {
        self::$authenticationToken = $authenticationToken;
    }

    /**
     * @throws AuthenticationException
     */
    public static function getAuthorizedUser(): User
    {
        if(self::$authorizedUser == null){
            throw new AuthenticationException();
        }

        return self::$authorizedUser;
    }

    public static function unAuthorizeUser(): void
    {
        if(self::$authenticationToken != null){
            self::$authenticationToken->setDateExpired(new \DateTime("now"));
            self::$authenticationTokenRepository->add(self::$authenticationToken);
        }
    }
}