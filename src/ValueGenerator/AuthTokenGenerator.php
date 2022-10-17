<?php

namespace App\ValueGenerator;

use App\Entity\User;

/**
 * AuthTokenGenerator
 *
 */
class AuthTokenGenerator implements ValueGeneratorInterface
{
    private User $userEntity;

    /**
     * @param User $userEntity
     */
    public function __construct(User $userEntity)
    {
        $this->userEntity = $userEntity;
    }

    public function generate(): string
    {
        $dateNow = (new \DateTime("now"))->getTimestamp();
        $userId = $this->userEntity->getId()->toBinary();
        $randomValue = rand(0, PHP_INT_MAX-1);

        $tokenToHash = $userId."-".$dateNow."#".$randomValue;

        return hash("sha512", $tokenToHash);
    }
}