<?php

declare(strict_types=1);

namespace App\ValueGenerator;

class PasswordHashGenerator implements ValueGeneratorInterface
{
    private string $planeTextPassword;

    public function __construct(string $planeTextPassword = null)
    {
        if ($planeTextPassword === null) {
            $this->planeTextPassword = "";
            $chars = 'abcdefghijklmnoprstwxyz1234567890';

            for ($i = 0; $i < 10; $i++) {
                $this->planeTextPassword .= $chars[random_int(0, (strlen($chars) - 1))];
            }
        } else {
            $this->planeTextPassword = $planeTextPassword;
        }
    }

    public function generate(): string
    {
        return hash('sha512', $this->planeTextPassword);
    }

    public function getBeforeGenerate(): string
    {
        return $this->planeTextPassword;
    }
}
