<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminUserChangePhoneQuery
{
    #[Assert\NotNull(message: "UserId is null")]
    #[Assert\NotBlank(message: "UserId is blank")]
    #[Assert\Uuid]
    private Uuid $userId;

    #[Assert\NotNull(message: "NewPhone; is null")]
    #[Assert\NotBlank(message: "NewPhone is empty")]
    #[Assert\Type(type: "string")]
    private string $newPhone;

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
     * @return string
     */
    public function getNewPhone(): string
    {
        return $this->newPhone;
    }

    /**
     * @param string $newPhone
     */
    public function setNewPhone(string $newPhone): void
    {
        $this->newPhone = $newPhone;
    }

}