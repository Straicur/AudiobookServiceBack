<?php

declare(strict_types=1);

namespace App\ValueGenerator;

use Exception;

class CategoryKeyGenerator implements ValueGeneratorInterface
{
    public function generate(): string
    {
        $newGeneratedToken = random_bytes(16);

        return bin2hex($newGeneratedToken);
    }
}
