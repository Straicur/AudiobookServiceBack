<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseExceptionInterface
 *
 */
interface ResponseExceptionInterface
{
    /**
     * Function which return response value when throw an error
     *
     * @return Response
     */
    public function getResponse(): Response;
}