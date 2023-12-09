<?php

namespace App\ValueGenerator;

use App\Entity\User;
use App\Exception\GeneratorException;

/**
 * AuthTokenGenerator
 *
 */
class AuthTokenGenerator implements ValueGeneratorInterface
{
    private readonly User $userEntity;

    /**
     * @param User $userEntity
     */
    public function __construct(User $userEntity)
    {
        $this->userEntity = $userEntity;
    }

    /**
     * @throws GeneratorException
     */
    public function generate(): string
    {
        try {
            $dateNow = (new \DateTime("now"))->getTimestamp();
            $userId = $this->userEntity->getId()->toBinary();
            $randomValue = random_int(0, PHP_INT_MAX - 1);

            $tokenToHash = $userId . "-" . $dateNow . "#" . $randomValue;

            return hash("sha512", $tokenToHash);
        }
        catch (\Exception){
            throw new GeneratorException();
        }
    }
}