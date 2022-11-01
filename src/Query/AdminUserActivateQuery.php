<?php

namespace App\Query;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class AdminUserActivateQuery
{
    #[Assert\NotNull(message: "UserId is null")]
    #[Assert\NotBlank(message: "UserId is blank")]
    #[Assert\Uuid]
    private Uuid $userId;

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

}