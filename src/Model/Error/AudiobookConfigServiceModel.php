<?php

declare(strict_types=1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class AudiobookConfigServiceModel implements ModelInterface
{
    private string $error = 'Service not configured';

    private string $description = 'You need to configurate this service first ';

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