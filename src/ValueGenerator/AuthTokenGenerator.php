<?php

declare(strict_types = 1);

namespace App\ValueGenerator;

use App\Entity\User;
use App\Exception\GeneratorException;
use DateTime;
use Override;
use Throwable;

use const PHP_INT_MAX;

class AuthTokenGenerator implements ValueGeneratorInterface
{
    public function __construct(private readonly User $userEntity) {}

    #[Override]
    public function generate(): string
    {
        try {
            $dateNow = new DateTime()->getTimestamp();
            $userId = $this->userEntity->getId()->toBinary();
            $randomValue = random_int(0, PHP_INT_MAX - 1);

            $tokenToHash = $userId . '-' . $dateNow . '#' . $randomValue;

            return hash('sha512', $tokenToHash);
        } catch (Throwable) {
            throw new GeneratorException();
        }
    }
}
