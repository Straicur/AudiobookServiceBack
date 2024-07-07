<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class JsonDataInvalidModel implements ModelInterface
{
    private string $error = 'Invalid JSON Data';

    private string $expectingClass;

    private array $validationErrors;

    public function __construct(string $expectingClass, array $validationErrors = [])
    {
        $this->expectingClass = $expectingClass;
        $this->validationErrors = $validationErrors;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
    }

    public function getExpectingClass(): string
    {
        return $this->expectingClass;
    }

    public function setExpectingClass(string $expectingClass): void
    {
        $this->expectingClass = $expectingClass;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function setValidationErrors(array $validationErrors): void
    {
        $this->validationErrors = $validationErrors;
    }
}