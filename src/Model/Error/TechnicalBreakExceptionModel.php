<?php

namespace App\Model\Error;

/**
 * TechnicalBreakExceptionModel
 */
class TechnicalBreakExceptionModel implements ModelInterface
{
    private string $error = "Technical Break";

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