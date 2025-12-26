<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class AdminCategoryAddQuery
{
    #[Assert\NotNull(message: 'Name is null')]
    #[Assert\NotBlank(message: 'Name is empty')]
    #[Assert\Type(type: 'string')]
    private string $name;

    #[Assert\Collection(
        fields: [
            'parentId' => new Assert\NotBlank(allowNull: true),
        ],
        allowMissingFields: true,
    )]
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'parentId', type: 'string', example: 'UUID', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('parentId', $additionalData)) {
            $additionalData['parentId'] = Uuid::fromString($additionalData['parentId']);
        }

        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
