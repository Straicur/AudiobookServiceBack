<?php

namespace App\Query;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class AdminAudiobookDeleteQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    /**
     * @param string $audiobookId
     */
    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);;
    }
}