<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminUserChangePasswordQuery
{
    #[Assert\NotNull(message: 'UserId is null')]
    #[Assert\NotBlank(message: 'UserId is blank')]
    #[Assert\Uuid]
    private Uuid $userId;

    #[Assert\NotNull(message: 'NewPassword; is null')]
    #[Assert\NotBlank(message: 'NewPassword is empty')]
    #[Assert\Type(type: 'string')]
    private string $newPassword;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = Uuid::fromString($userId);
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }
}
