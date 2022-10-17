<?php

namespace App\Service;

use App\Entity\User;

/**
 * AuthorizedUserServiceInterface
 *
 */
interface AuthorizedUserServiceInterface
{
    public static function getAuthorizedUser(): User;

    public static function unAuthorizeUser(): void;
}