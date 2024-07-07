<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class CacheExceptionModel implements ModelInterface
{
    private string $error = 'Cache key or tag not found';

    private string $description = 'Contact us with this error';

    public function __construct()
    {
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
