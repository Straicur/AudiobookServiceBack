<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestServiceInterface
 *
 */
interface RequestServiceInterface
{
    public function getRequestBodyContent(Request $request, string $className): object;
}