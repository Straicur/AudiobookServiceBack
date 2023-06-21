<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * TranslateServiceInterface
 *
 */
interface TranslateServiceInterface
{
    public function getTranslation(Request $request, string $message): string;
}