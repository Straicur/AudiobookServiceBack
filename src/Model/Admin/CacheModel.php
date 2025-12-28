<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class CacheModel implements ModelInterface
{
    public function __construct(private string $value) {}

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
