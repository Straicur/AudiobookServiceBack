<?php

declare(strict_types = 1);

namespace App\Model\Admin;

class AdminSystemRoleModel
{
    public function __construct(private string $name, private int $type) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }
}
