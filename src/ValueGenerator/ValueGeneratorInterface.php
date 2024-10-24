<?php

namespace App\ValueGenerator;

interface ValueGeneratorInterface
{
    /**
     * Function used to generate value
     *
     * @return string|int|array|object|float|bool|null
     */
    public function generate(): string|int|array|object|float|bool|null;
}
