<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AudiobookCoverModel
{
    private string $id;
    private string $url;

    public function __construct(string $id, string $url)
    {
        $this->id = $id;
        $this->url = $url;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
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
