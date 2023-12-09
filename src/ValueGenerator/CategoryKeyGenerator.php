<?php

namespace App\ValueGenerator;

use Exception;

/**
 * CategoryKeyGenerator
 */
class CategoryKeyGenerator implements ValueGeneratorInterface
{
    /**
     * @return string
     * @throws Exception
     */
    public function generate(): string
    {
        $newGeneratedToken = random_bytes(16);

        return bin2hex($newGeneratedToken);
    }
}