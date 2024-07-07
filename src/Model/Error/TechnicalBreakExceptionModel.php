<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class TechnicalBreakExceptionModel implements ModelInterface
{
    private string $error = 'Technical Break';

    public function __construct()
    {
    }

    public function getError(): string
    {
        return $this->error;
    }
}
