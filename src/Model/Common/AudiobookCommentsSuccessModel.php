<?php

namespace App\Model\Common;

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

    /**
     * @param AudiobookCommentsModel $category
     * @return void
     */
    public function addComment(AudiobookCommentsModel $category)
    {
        $this->comments[] = $category;
    }
}