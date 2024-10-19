<?php

namespace App\Service\User;

use App\Entity\AuthenticationToken;
use App\Entity\User;
use App\Entity\UserInformation;
use Symfony\Component\HttpFoundation\Request;

interface UserLoginServiceInterface
{
    public function getUserInformation(string $email, Request $request): UserInformation;

    public function getValidUser(UserInformation $userInformation, Request $request): User;

    public function loginToService(UserInformation $userInformation, Request $request, string $password): void;

    public function getAuthenticationToken($user): AuthenticationToken;

    public function resetLoginAttempts(UserInformation $userInformation): void;
}