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

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}