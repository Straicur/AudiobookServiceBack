<?php

declare(strict_types = 1);

namespace App\Service;

use App\Annotation\AuthValidation;
use App\Entity\AuthenticationToken;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface AuthValidationServiceInterface
{
    public function getAuthenticatedUserToken(Request $request): AuthenticationToken;

    public function checkIfUserIsDeleted(User $user): void;

    public function checkTechnicalBreak(User $user): void;

    public function checkIfUserIsBanned(User $user, AuthenticationToken $authToken): void;

    public function addAuthTokenTime(AuthenticationToken $authToken): void;

    public function checkIfUserHasRoles(User $user, AuthValidation $authValidationAttribute): void;
}
