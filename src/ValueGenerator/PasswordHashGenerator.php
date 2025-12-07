<?php

declare(strict_types = 1);

namespace App\ValueGenerator;

use Override;

use function strlen;

class PasswordHashGenerator implements ValueGeneratorInterface
{
    private string $planeTextPassword;

    public function __construct(?string $planeTextPassword = null)
    {
        if (null === $planeTextPassword) {
            $this->planeTextPassword = '';
            $chars = 'abcdefghijklmnoprstwxyz1234567890';

            for ($i = 0; 10 > $i; ++$i) {
                $this->planeTextPassword .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } else {
            $this->planeTextPassword = $planeTextPassword;
        }
    }

    #[Override]
    public function generate(): string
    {
        return hash('sha512', $this->planeTextPassword);
    }
}
