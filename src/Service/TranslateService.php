<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslateService implements TranslateServiceInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getTranslation(Request $request, string $message): string
    {
        $preferredLanguage = $request->getPreferredLanguage();

        return $this->translator->trans($message, locale: $preferredLanguage);
    }
}