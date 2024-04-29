<?php

declare(strict_types=1);

namespace App\ValueGenerator;

/**
 * RegisterCodeGenerator
 */
class RegisterCodeGenerator implements ValueGeneratorInterface
{
    private string $registerCode;

    /**
     * @throws \Exception
     */
    public function __construct(string $registerCode = null)
    {
        if ($registerCode === null) {
            $this->registerCode = "";
            $chars = 'abcdefghijklmnoprstwxyz1234567890';

            for ($i = 0; $i < 15; $i++) {
                $this->registerCode .= $chars[random_int(0, (strlen($chars) - 1))];
            }
        } else {
            $this->registerCode = $registerCode;
        }
    }

    public function generate(): string
    {
        return hash('sha512', $this->registerCode);
    }

    public function getBeforeGenerate(): string
    {
        return $this->registerCode;
    }
}