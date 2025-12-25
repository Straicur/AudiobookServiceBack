<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use App\Enums\AudiobookAgeRange;
use App\Enums\AudiobookOrderSearch;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class AdminAudiobooksQuery
{
    #[Assert\NotNull(message: 'Page is null')]
    #[Assert\NotBlank(message: 'Page is empty')]
    #[Assert\Type(type: 'integer')]
    private int $page;

    #[Assert\NotNull(message: 'Limit is null')]
    #[Assert\NotBlank(message: 'Limit is empty')]
    #[Assert\Type(type: 'integer')]
    private int $limit;

    #[Assert\Collection(
        fields: [
            'categories'=> new Assert\All([
                new Assert\NotBlank()
            ]),
            'author'=> new Assert\NotBlank(allowNull: true),
            'title'=> new Assert\NotBlank(allowNull: true),
            'album'=> new Assert\NotBlank(allowNull: true),
            'duration'=> new Assert\NotBlank(allowNull: true),
            'parts'=> new Assert\NotBlank(allowNull: true),
            'age'=> new Assert\NotBlank(allowNull: true),
            'order'=> new Assert\NotBlank(allowNull: true),
            'year'=> new Assert\NotBlank(allowNull: true)
        ],
        allowMissingFields: true,
    )]
    protected array $searchData = [];

    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'categories', type: 'array', items: new OA\Items(type: 'string', example: 'UUID'), nullable: true),
        new OA\Property(property: 'author', type: 'string', example: 'author', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'title', nullable: true),
        new OA\Property(property: 'album', type: 'string', example: 'album', nullable: true),
        new OA\Property(property: 'duration', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'parts', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'age', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'year', type: 'string', example: 'd.m.Y', nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (
            array_key_exists('age', $searchData)
            && $searchData['age'] !== AudiobookAgeRange::FROM3TO7->value
            && $searchData['age'] !== AudiobookAgeRange::FROM7TO12->value
            && $searchData['age'] !== AudiobookAgeRange::FROM12TO16->value
            && $searchData['age'] !== AudiobookAgeRange::FROM16TO18->value
            && $searchData['age'] !== AudiobookAgeRange::ABOVE18->value
        ) {
            $searchData['age'] = AudiobookAgeRange::FROM12TO16->value;
        }

        if (
            array_key_exists('order', $searchData)
            && $searchData['order'] !== AudiobookOrderSearch::POPULAR->value
            && $searchData['order'] !== AudiobookOrderSearch::LEST_POPULAR->value
            && $searchData['order'] !== AudiobookOrderSearch::LATEST->value
            && $searchData['order'] !== AudiobookOrderSearch::OLDEST->value
            && $searchData['order'] !== AudiobookOrderSearch::ALPHABETICAL_ASC->value
            && $searchData['order'] !== AudiobookOrderSearch::ALPHABETICAL_DESC->value
            && $searchData['order'] !== AudiobookOrderSearch::TOP_RATED->value
            && $searchData['order'] !== AudiobookOrderSearch::WORST_RATED->value
        ) {
            $searchData['order'] = AudiobookOrderSearch::POPULAR->value;
        }

        $this->searchData = $searchData;
    }

    public function getSearchData(): array
    {
        return $this->searchData;
    }

    #[OA\Property(type: 'integer', example: 0)]
    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    #[OA\Property(type: 'integer', example: 10)]
    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
