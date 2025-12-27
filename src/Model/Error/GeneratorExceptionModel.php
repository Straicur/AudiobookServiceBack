<?php

declare(strict_types = 1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class GeneratorExceptionModel implements ModelInterface
{
    private string $error = 'Service generator error';

    private string $description = 'Please contact us';

    public function getError(): string
    {
        return $this->error;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
