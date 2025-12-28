<?php

declare(strict_types = 1);

namespace App\Model\Common;

class AudiobookCommentModel
{
    public function __construct(private string $email, private string $name) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
