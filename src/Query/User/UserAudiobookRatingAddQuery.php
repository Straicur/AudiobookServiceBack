<?php

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class UserAudiobookRatingAddQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: "CategoryKey is null")]
    #[Assert\NotBlank(message: "CategoryKey is empty")]
    #[Assert\Type(type: "string")]
    private string $categoryKey;

    #[Assert\NotNull(message: "Rating is null")]
    #[Assert\Type(type: "boolean")]
    private bool $rating;

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
     * @return bool
     */
    public function isRating(): bool
    {
        return $this->rating;
    }

    /**
     * @param bool $rating
     */
    public function setRating(bool $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @return string
     */
    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    /**
     * @param string $categoryKey
     */
    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }
}