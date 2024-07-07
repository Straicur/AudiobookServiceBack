<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AudiobookCommentsSuccessModel implements ModelInterface
{
    /**
     * @var AudiobookCommentsModel[]
     */
    private array $comments;

    public function __construct(array $comments)
    {
        $this->comments = $comments;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function addComment(AudiobookCommentsModel $category): void
    {
        $this->comments[] = $category;
    }
}
