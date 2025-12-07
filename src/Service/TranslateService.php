<?php

declare(strict_types = 1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslateService implements TranslateServiceInterface
{
    private ?string $preferredLanguage = null;

    public function __construct(private readonly TranslatorInterface $translator) {}

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
        if (null === $this->preferredLanguage) {
            $this->preferredLanguage = $this->getLocate();
        }

        return $this->translator->trans($message, locale: $this->preferredLanguage);
    }
}
