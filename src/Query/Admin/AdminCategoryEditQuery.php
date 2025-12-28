<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminCategoryEditQuery
{
    #[Assert\NotNull(message: 'Name is null')]
    #[Assert\NotBlank(message: 'Name is empty')]
    #[Assert\Type(type: 'string')]
    private string $name;

    #[Assert\NotNull(message: 'CategoryId is null')]
    #[Assert\NotBlank(message: 'CategoryId is empty')]
    #[Assert\Uuid]
    private Uuid $categoryId;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getCategoryId(): Uuid
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = Uuid::fromString($categoryId);
    }
}
