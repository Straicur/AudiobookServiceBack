<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslateService implements TranslateServiceInterface
{
    private TranslatorInterface $translator;

    private ?string $preferredLanguage = null;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setPreferredLanguage(Request $request): void
    {
        $this->preferredLanguage = $request->getPreferredLanguage();
    }

    public function getLocate(): string
    {
        return $this->translator->getLocale();
    }

    public function getTranslation(string $message): string
    {
        if ($this->preferredLanguage == null) {
            $this->preferredLanguage = self::getLocate();
        }

        return $this->translator->trans($message, locale: $this->preferredLanguage);
    }
}