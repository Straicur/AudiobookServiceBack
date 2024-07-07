<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AudiobookPartSuccessModel implements ModelInterface
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
