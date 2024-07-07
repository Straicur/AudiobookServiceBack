<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ForceTranslationExtension extends AbstractExtension
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
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