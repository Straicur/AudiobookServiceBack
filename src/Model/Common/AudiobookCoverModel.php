<?php

declare(strict_types = 1);

namespace App\Model\Common;

class AudiobookCoverModel
{
    public function __construct(private string $id, private string $url) {}

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
