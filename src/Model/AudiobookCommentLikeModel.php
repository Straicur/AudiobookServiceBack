<?php

namespace App\Model;

class AudiobookCommentLikeModel
{
    private string $id;
    private bool $like;

    /**
     * @param string $id
     * @param bool $like
     */
    public function __construct(string $id, bool $like)
    {
        $this->id = $id;
        $this->like = $like;
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
     * @return bool
     */
    public function isLike(): bool
    {
        return $this->like;
    }

    /**
     * @param bool $like
     */
    public function setLike(bool $like): void
    {
        $this->like = $like;
    }

}