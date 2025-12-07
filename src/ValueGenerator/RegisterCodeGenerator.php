<?php

declare(strict_types = 1);

namespace App\ValueGenerator;

use Override;

use function strlen;

class RegisterCodeGenerator implements ValueGeneratorInterface
{
    private string $registerCode;

    public function __construct(?string $registerCode = null)
    {
        if (null === $registerCode) {
            $this->registerCode = '';
            $chars = 'abcdefghijklmnoprstwxyz1234567890';

            for ($i = 0; 15 > $i; ++$i) {
                $this->registerCode .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } else {
            $this->registerCode = $registerCode;
        }
    }

    #[Override]
    public function generate(): string
    {
        return hash('sha512', $this->registerCode);
    }

    public function getBeforeGenerate(): string
    {
        return $this->registerCode;
    }
}
