<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface TranslateServiceInterface
{
    public function setPreferredLanguage(Request $request): void;
    public function getTranslation(string $message): string;
}