<?php

declare(strict_types = 1);

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class UserAudiobookCommentLikeAddQuery
{
    #[Assert\NotNull(message: 'CommentId is null')]
    #[Assert\NotBlank(message: 'CommentId is blank')]
    #[Assert\Uuid]
    private Uuid $commentId;

    #[Assert\NotNull(message: 'Like is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $like;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getCommentId(): Uuid
    {
        return $this->commentId;
    }

    public function setCommentId(string $commentId): void
    {
        $this->commentId = Uuid::fromString($commentId);
    }

    public function isLike(): bool
    {
        return $this->like;
    }

    public function setLike(bool $like): void
    {
        $this->like = $like;
    }
}
