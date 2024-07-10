<?php

namespace App\Query\Admin;

use App\Enums\AudiobookAgeRange;
use App\Enums\AudiobookOrderSearch;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

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

    protected array $searchData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('searchData', new Assert\Collection([
            'fields' => [
                'categories' => new Assert\Optional([
                    new Assert\All(constraints: [
                        new Assert\NotBlank(message: 'Categories is empty'),
                        new Assert\Regex(pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', message: 'Bad Uuid'),
                        new Assert\Uuid(),
                    ]),
                ]),
                'author'     => new Assert\Optional([
                    new Assert\NotBlank(message: 'Author is empty'),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'title'      => new Assert\Optional([
                    new Assert\NotBlank(message: 'Title is empty'),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'album'      => new Assert\Optional([
                    new Assert\NotBlank(message: 'Album is empty'),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'duration'   => new Assert\Optional([
                    new Assert\NotBlank(message: 'Duration is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                ]),
                'parts'      => new Assert\Optional([
                    new Assert\NotBlank(message: 'Parts is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                ]),
                'age'        => new Assert\Optional([
                    new Assert\NotBlank(message: 'Age is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(6),
                ]),
                'order'      => new Assert\Optional([
                    new Assert\NotBlank(message: 'Order is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(9),
                ]),
                'year'       => new Assert\Optional([
                    new Assert\NotBlank(message: 'Year is empty'),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
            ],
        ]));
    }

    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'categories', type: 'array', nullable: true, attachables: [
            new OA\Items(type: 'string', example: 'UUID'),
        ]),
        new OA\Property(property: 'author', type: 'string', example: 'author', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'title', nullable: true),
        new OA\Property(property: 'album', type: 'string', example: 'album', nullable: true),
        new OA\Property(property: 'duration', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'parts', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'age', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'year', type: 'datetime', example: 'd.m.Y', nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (
            array_key_exists('age', $searchData) &&
            $searchData['age'] !== AudiobookAgeRange::FROM3TO7->value &&
            $searchData['age'] !== AudiobookAgeRange::FROM7TO12->value &&
            $searchData['age'] !== AudiobookAgeRange::FROM12TO16->value &&
            $searchData['age'] !== AudiobookAgeRange::FROM16TO18->value &&
            $searchData['age'] !== AudiobookAgeRange::ABOVE18->value
        ) {
            $searchData['age'] = AudiobookAgeRange::FROM12TO16->value;
        }

        if (
            array_key_exists('order', $searchData) &&
            $searchData['order'] !== AudiobookOrderSearch::POPULAR->value &&
            $searchData['order'] !== AudiobookOrderSearch::LEST_POPULAR->value &&
            $searchData['order'] !== AudiobookOrderSearch::LATEST->value &&
            $searchData['order'] !== AudiobookOrderSearch::OLDEST->value &&
            $searchData['order'] !== AudiobookOrderSearch::ALPHABETICAL_ASC->value &&
            $searchData['order'] !== AudiobookOrderSearch::ALPHABETICAL_DESC->value &&
            $searchData['order'] !== AudiobookOrderSearch::TOP_RATED->value &&
            $searchData['order'] !== AudiobookOrderSearch::WORST_RATED->value
        ) {
            $searchData['order'] = AudiobookOrderSearch::POPULAR->value;
        }

        if (array_key_exists('year', $searchData)) {
            $searchData['year'] = DateTime::createFromFormat('d.m.Y', $searchData['year']);
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
