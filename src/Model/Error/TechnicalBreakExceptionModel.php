<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

/**
 * TechnicalBreakExceptionModel
 */
class TechnicalBreakExceptionModel implements ModelInterface
{
    private string $error = 'Technical Break';

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

}