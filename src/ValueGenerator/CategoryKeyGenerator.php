<?php

declare(strict_types = 1);

namespace App\ValueGenerator;

use Override;

class CategoryKeyGenerator implements ValueGeneratorInterface
{
    #[Override]
    public function generate(): string
    {
        $newGeneratedToken = random_bytes(16);

        return bin2hex($newGeneratedToken);
    }
}
