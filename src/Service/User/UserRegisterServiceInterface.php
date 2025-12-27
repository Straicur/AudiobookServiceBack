<?php

declare(strict_types = 1);

namespace App\Service\User;

use App\Entity\User;
use App\Query\Common\RegisterQuery;
use Symfony\Component\HttpFoundation\Request;

interface UserRegisterServiceInterface
{
    public function checkExistingUsers(RegisterQuery $registerQuery, Request $request): void;

    public function checkInstitutionLimits(Request $request): void;

    public function createUser(RegisterQuery $registerQuery): User;

    public function getRegisterCode(User $newUser): string;

    public function sendMail(User $newUser, string $registerCode, Request $request): void;
}
