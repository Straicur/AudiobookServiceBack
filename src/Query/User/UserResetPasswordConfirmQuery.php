<?php

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class UserResetPasswordConfirmQuery
{
    #[Assert\NotNull(message: 'UserId is null')]
    #[Assert\NotBlank(message: 'UserId is blank')]
    #[Assert\Uuid]
    private Uuid $userId;

    #[Assert\NotNull(message: 'Password is null')]
    #[Assert\NotBlank(message: 'Password is empty')]
    #[Assert\Type(type: 'string')]
    private string $password;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = Uuid::fromString($userId);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
