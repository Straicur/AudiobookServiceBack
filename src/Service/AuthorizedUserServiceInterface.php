<?php

namespace App\Service;

use App\Entity\AuthenticationToken;
use App\Entity\User;

interface AuthorizedUserServiceInterface
{
    public static function getAuthorizedUser(): User;

    public static function setAuthorizedUser(User $user): void;

    public static function setAuthenticationToken(?AuthenticationToken $authenticationToken): void;

    public static function unAuthorizeUser(): void;
}
