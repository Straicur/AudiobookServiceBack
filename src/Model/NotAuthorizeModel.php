<?php

namespace App\Model;

/**
 * NotAuthorizedModel
 *
 * Model which contains all fields returned in response when user authentication requirements are not passed
 *
 */
class NotAuthorizeModel implements ModelInterface
{
    private string $error = "User not authorized";

    private string $description = "Authorization token could be NULL, invalid or expired";

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