<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class GeneratorExceptionModel implements ModelInterface
{
    private string $error = 'Service generator error';

    private string $description = 'Please contact us';

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