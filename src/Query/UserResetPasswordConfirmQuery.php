<?php

namespace App\Query;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class UserResetPasswordConfirmQuery
{
    #[Assert\NotNull(message: "UserId is null")]
    #[Assert\NotBlank(message: "UserId is blank")]
    #[Assert\Uuid]
    private Uuid $userId;

    #[Assert\NotNull(message: "Password is null")]
    #[Assert\NotBlank(message: "Password is empty")]
    #[Assert\Type(type: "string")]
    private string $password;

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
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

}