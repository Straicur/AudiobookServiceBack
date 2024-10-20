<?php

namespace App\Query\Admin;

use App\Enums\TechnicalBreakOrder;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminTechnicalBreakListQuery
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
                'nameOrLastname' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'active'   => new Assert\Optional([
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'order'    => new Assert\Optional([
                    new Assert\NotBlank(message: 'Order is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThanOrEqual(1),
                    new Assert\LessThanOrEqual(3),
                ]),
                'dateFrom' => new Assert\Optional([
                    new Assert\NotBlank(message: 'DateFrom is empty'),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'dateTo'   => new Assert\Optional([
                    new Assert\NotBlank(message: 'DateTo is empty'),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
            ],
        ]));
    }

    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'nameOrLastname', type: 'string', example: 'UUID', nullable: true),
        new OA\Property(property: 'active', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'dateFrom', type: 'string', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'dateTo', type: 'string', example: 'd.m.Y', nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (
            array_key_exists('order', $searchData) &&
            $searchData['order'] !== TechnicalBreakOrder::LATEST->value &&
            $searchData['order'] !== TechnicalBreakOrder::OLDEST->value
        ) {
            $searchData['order'] = TechnicalBreakOrder::LATEST->value;
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
