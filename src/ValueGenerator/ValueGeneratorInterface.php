<?php

declare(strict_types = 1);

namespace App\ValueGenerator;

interface ValueGeneratorInterface
{
    /**
     * Function used to generate value.
     */
    public function generate(): string|int|array|object|float|bool|null;
}
