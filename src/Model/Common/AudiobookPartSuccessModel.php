<?php

declare(strict_types = 1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AudiobookPartSuccessModel implements ModelInterface
{
    public function __construct(private string $url) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
