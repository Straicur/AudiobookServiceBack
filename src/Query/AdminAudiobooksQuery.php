<?php

namespace App\Query;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminAudiobooksQuery
{
    #[Assert\NotNull(message: "Page is null")]
    #[Assert\NotBlank(message: "Page is empty")]
    #[Assert\Type(type: "integer")]
    private int $page;

    #[Assert\NotNull(message: "Limit is null")]
    #[Assert\NotBlank(message: "Limit is empty")]
    #[Assert\Type(type: "integer")]
    private int $limit;

    protected array $searchData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('searchData', new Assert\Collection([
            'fields' => [
                'searchCategories' => new Assert\Optional([
                    new Assert\NotBlank(message: 'SearchCategories is empty'),
                    new Assert\All(constraints: [
                        new Assert\NotBlank(),
                        new Assert\Regex(pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', message: 'Bad Uuid'),
                        new Assert\Uuid(),
                    ]),
                ]),
                'areaFrom' => new Assert\Optional([
                    new Assert\NotBlank(message: 'AreaFrom is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'areaTo' => new Assert\Optional([
                    new Assert\NotBlank(message: 'AreaFrom is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'priceFrom' => new Assert\Optional([
                    new Assert\NotBlank(message: 'PriceFrom is empty'),
                    new Assert\Type(type: 'integer'),
                ]),
                'priceTo' => new Assert\Optional([
                    new Assert\NotBlank(message: 'AreaFrom is empty'),
                    new Assert\Type(type: 'integer'),
                ]),
                'roomsFrom' => new Assert\Optional([
                    new Assert\NotBlank(message: 'AreaFrom is empty'),
                    new Assert\Type(type: 'integer'),
                ]),
                'roomsTo' => new Assert\Optional([
                    new Assert\NotBlank(message: 'AreaFrom is empty'),
                    new Assert\Type(type: 'integer'),
                ]),
                'city' => new Assert\Optional([
                    new Assert\NotBlank(message: 'City is empty'),
                    new Assert\NotNull(),
                    new Assert\Uuid(),
                ]),
                'cityDistrict' => new Assert\Optional([
                    new Assert\NotBlank(message: 'CityDistrict is empty'),
                    new Assert\NotNull(),
                    new Assert\Uuid(),
                ]),
                'propertyType' => new Assert\Optional([
                    new Assert\NotBlank(message: 'PropertyType is empty'),
                    new Assert\NotNull(),
                    new Assert\Uuid(),
                ]),
                'architecture' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Architecture is empty'),
                    new Assert\NotNull(),
                    new Assert\Uuid(),
                ]),
                'buildingFinishStandard' => new Assert\Optional([
                    new Assert\NotBlank(message: 'BuildingFinishStandard is empty'),
                    new Assert\NotNull(),
                    new Assert\Uuid(),
                ]),
                'rentDateStart' => new Assert\Optional([
                    new Assert\NotBlank(message: 'RentDateStart is empty'),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
            ],
        ]));
    }

    /**
     * @param string[] $searchData
     */
    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'categories', type: 'array', nullable: true, attachables: [
            new OA\Items(type: 'string', example: 'UUID'),
        ]),

        new OA\Property(property: 'author', type: 'string', example: 'author', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'title', nullable: true),
        new OA\Property(property: 'album', type: 'string', example: 'album', nullable: true),
        new OA\Property(property: 'parts', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'age', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'year', type: 'datetime', example: 'd.m.Y', nullable: true),
    ], type: 'object')]
    public function setSearchData(array $searchData): void
    {
        if (array_key_exists('city', $searchData)) {
            $searchData['city'] = Uuid::fromString($searchData['city']);
        }
        if (array_key_exists('cityDistrict', $searchData)) {
            $searchData['cityDistrict'] = Uuid::fromString($searchData['cityDistrict']);
        }
        if (array_key_exists('propertyType', $searchData)) {
            $searchData['propertyType'] = Uuid::fromString($searchData['propertyType']);
        }
        if (array_key_exists('architecture', $searchData)) {
            $searchData['architecture'] = Uuid::fromString($searchData['architecture']);
        }
        if (array_key_exists('buildingFinishStandard', $searchData)) {
            $searchData['buildingFinishStandard'] = Uuid::fromString($searchData['buildingFinishStandard']);
        }
        if (array_key_exists('rentDateStart', $searchData)) {
            $searchData['rentDateStart'] = \DateTime::createFromFormat('d.m.Y', $searchData['rentDateStart']);
        }

        $this->searchData = $searchData;
    }

    /**
     * @return string[]
     */
    public function getSearchData(): array
    {
        return $this->searchData;
    }

    /**
     * @return int
     */
    #[OA\Property(type: "integer", example: 0)]
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    #[OA\Property(type: "integer", example: 10)]
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

}