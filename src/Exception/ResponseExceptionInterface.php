<?php

declare(strict_types = 1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

interface ResponseExceptionInterface
{
    /**
     * Function which return response value when throw an error.
     */
    public function getResponse(): Response;
}
