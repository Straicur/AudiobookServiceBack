<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminCategoryAddAudiobookQuery
{
    #[Assert\NotNull(message: 'CategoryId is null')]
    #[Assert\NotBlank(message: 'CategoryId is empty')]
    #[Assert\Uuid]
    private Uuid $categoryId;

    #[Assert\NotNull(message: 'AudiobookId is null')]
    #[Assert\NotBlank(message: 'AudiobookId is empty')]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getCategoryId(): Uuid
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = Uuid::fromString($categoryId);
    }

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);
    }
}
