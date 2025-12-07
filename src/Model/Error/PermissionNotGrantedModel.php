<?php

declare(strict_types = 1);

namespace App\Model\Error;

use App\Model\ModelInterface;

class PermissionNotGrantedModel implements ModelInterface
{
    private string $error = 'Permission not granted';

    private string $description = "Authorized user don't have permission to do this";

    public function getError(): string
    {
        return $this->error;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
