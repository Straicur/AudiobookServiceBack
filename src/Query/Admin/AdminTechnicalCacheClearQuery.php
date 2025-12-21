<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class AdminTechnicalCacheClearQuery
{
    #[Assert\Collection(
        fields: [
            'pools'=> new Assert\All([
                new Assert\NotBlank()
            ]),
            'admin'=> new Assert\NotNull(),
            'user'=> new Assert\NotNull(),
            'all'=> new Assert\NotNull()
        ],
        allowMissingFields: true,
    )]
    protected array $cacheData = [];

    #[OA\Property(property: 'cacheData', properties: [
        new OA\Property(property: 'pools', type: 'array', nullable: true, attachables: [
            new OA\Items(type: 'string', example: 'Admin...'),
        ]),
        new OA\Property(property: 'admin', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'user', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'all', type: 'boolean', example: true, nullable: true),
    ], type    : 'object')]
    public function setCacheData(array $cacheData): void
    {
        $this->cacheData = $cacheData;
    }

    public function getCacheData(): array
    {
        return $this->cacheData;
    }
}
