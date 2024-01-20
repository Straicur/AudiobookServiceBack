<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminTechnicalCacheClearQuery
{
    protected array $cacheData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('cacheData', new Assert\Collection([
            'fields' => [
                'pools' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Pools is empty'),
                    new Assert\All(constraints: [
                        new Assert\NotBlank(message: 'Album is empty'),
                        new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                    ]),
                ]),
                'admin' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Admin is empty'),
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'user' => new Assert\Optional([
                    new Assert\NotBlank(message: 'User is empty'),
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'all' => new Assert\Optional([
                    new Assert\NotBlank(message: 'All is empty'),
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ])
            ],
        ]));
    }

    /**
     * @param string[] $cacheData
     */
    #[OA\Property(property: 'cacheData', properties: [
        new OA\Property(property: 'pools', type: 'array', nullable: true, attachables: [
            new OA\Items(type: 'string', example: 'Admin...'),
        ]),
        new OA\Property(property: 'admin', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'user', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'all', type: 'boolean', example: true, nullable: true),
    ], type: 'object')]
    public function setCacheData(array $cacheData): void
    {
        $this->cacheData = $cacheData;
    }

    /**
     * @return string[]
     */
    public function getCacheData(): array
    {
        return $this->cacheData;
    }

}