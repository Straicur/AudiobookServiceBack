<?php

declare(strict_types = 1);

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class UserAudiobookInfoAddQuery
{
    #[Assert\NotNull(message: 'AudiobookId is null')]
    #[Assert\NotBlank(message: 'AudiobookId is blank')]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: 'CategoryKey is null')]
    #[Assert\NotBlank(message: 'CategoryKey is empty')]
    #[Assert\Type(type: 'string')]
    private string $categoryKey;

    #[Assert\NotNull(message: 'Part is null')]
    #[Assert\NotBlank(message: 'Part is empty')]
    #[Assert\Type(type: 'integer')]
    private int $part;

    #[Assert\NotNull(message: 'EndedTime is null')]
    #[Assert\NotBlank(message: 'EndedTime is empty')]
    #[Assert\Type(type: 'integer')]
    private int $endedTime;

    #[Assert\NotNull(message: 'Watched is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $watched;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);
    }

    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

    public function getPart(): int
    {
        return $this->part;
    }

    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    public function getEndedTime(): int
    {
        return $this->endedTime;
    }

    public function setEndedTime(int $endedTime): void
    {
        $this->endedTime = $endedTime;
    }

    public function getWatched(): bool
    {
        return $this->watched;
    }

    public function setWatched(bool $watched): void
    {
        $this->watched = $watched;
    }
}
