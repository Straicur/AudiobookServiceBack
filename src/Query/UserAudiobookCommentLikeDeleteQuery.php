<?php

namespace App\Query;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class UserAudiobookCommentLikeDeleteQuery
{
    #[Assert\NotNull(message: "CommentId is null")]
    #[Assert\NotBlank(message: "CommentId is blank")]
    #[Assert\Uuid]
    private Uuid $commentLikeId;

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getCommentLikeId(): Uuid
    {
        return $this->commentLikeId;
    }

    /**
     * @param string $commentLikeId
     */
    public function setCommentLikeId(string $commentLikeId): void
    {
        $this->commentLikeId = Uuid::fromString($commentLikeId);;
    }
}