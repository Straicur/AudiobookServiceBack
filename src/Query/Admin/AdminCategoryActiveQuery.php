<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminCategoryActiveQuery
{
    #[Assert\NotNull(message: 'CategoryId is null')]
    #[Assert\NotBlank(message: 'CategoryId is empty')]
    #[Assert\Uuid]
    private Uuid $categoryId;

    #[Assert\NotNull(message: 'Active is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $active;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getCategoryId(): Uuid
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = Uuid::fromString($categoryId);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
