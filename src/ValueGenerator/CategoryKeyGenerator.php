<?php

namespace App\ValueGenerator;

/**
 * CategoryKeyGenerator
 */
class CategoryKeyGenerator implements ValueGeneratorInterface
{
    public function generate(): string
    {
        $newGeneratedToken = openssl_random_pseudo_bytes(16);
        return bin2hex($newGeneratedToken);
    }
}