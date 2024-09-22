<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ModelInterface;

class UserChangeCodeSuccessModel implements ModelInterface
{
    private string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
