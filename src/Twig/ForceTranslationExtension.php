<?php

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ForceTranslationExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('force_translate', [$this, 'forceTranslate']),
        ];
    }

    public function forceTranslate($message, $locale, $variables = []): string
    {
        return $this->translator->trans($message, $variables, null, $locale);
    }
}