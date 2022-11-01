<?php

namespace App\Query;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class AdminUserBanQuery
{
    #[Assert\NotNull(message: "UserId is null")]
    #[Assert\NotBlank(message: "UserId is blank")]
    #[Assert\Uuid]
    private Uuid $userId;

    #[Assert\NotNull(message: "Banned is null")]
    #[Assert\Type(type: "boolean")]
    private bool $banned;

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = Uuid::fromString($userId);;
    }

    /**
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     */
    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }

}