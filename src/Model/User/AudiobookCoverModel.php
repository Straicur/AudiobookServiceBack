<?php

namespace App\Model\User;

use App\Model\ModelInterface;

class AudiobookCoverModel implements ModelInterface
{
    private string $id;
    private string $url;

    /**
     * @param string $id
     * @param string $url
     */
    public function __construct(string $id, string $url)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function seturlUrl(string $url): void
    {
        $this->url = $url;
    }
}
