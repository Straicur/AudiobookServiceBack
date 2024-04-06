<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminAudiobookCommentDeleteQuery
{
    #[Assert\NotNull(message: 'AudiobookCommentId is null')]
    #[Assert\NotBlank(message: 'AudiobookCommentId is blank')]
    #[Assert\Uuid]
    private Uuid $audiobookCommentId;

    /**
     * @return Uuid
     */
    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getAudiobookCommentId(): Uuid
    {
        return $this->audiobookCommentId;
    }

    /**
     * @param string $audiobookCommentId
     */
    public function setAudiobookCommentId(string $audiobookCommentId): void
    {
        $this->audiobookCommentId = Uuid::fromString($audiobookCommentId);;
    }
}