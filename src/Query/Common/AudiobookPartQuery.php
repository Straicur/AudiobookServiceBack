<?php

namespace App\Query\Common;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AudiobookPartQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: "Part is null")]
    #[Assert\NotBlank(message: "Part is empty")]
    #[Assert\Type(type: "integer")]
    private int $part;

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

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart(int $part): void
    {
        $this->part = $part;
    }

}