<?php

namespace App\Model\User;

use App\Model\Error\ModelInterface;

class AudiobookCommentsSuccessModel implements ModelInterface
{
    /**
     * @var AudiobookCommentsModel[]
     */
    private array $comments = [];

    /**
     * @param AudiobookCommentsModel[] $comments
     */
    public function __construct(array $comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return AudiobookCommentsModel[]
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param array $comments
     */
    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function addComment(AudiobookCommentsModel $category)
    {
        $this->comments[] = $category;
    }
}