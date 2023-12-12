<?php

namespace App\Model\Common;

use App\Model\Error\ModelInterface;

class AudiobookPartSuccessModel implements ModelInterface
{
    private string $url;

    /**
     * @param string $url
     */
    public function __construct( string $url)
    {
        $this->url = $url;
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
