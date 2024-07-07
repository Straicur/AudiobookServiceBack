<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class ServiceUnavailableModel implements ModelInterface
{
    private string $error = 'Current service is unavailable';

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}