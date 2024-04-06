<?php

namespace App\Query\Admin;

use App\Enums\TechnicalBreakOrder;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
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
                'userId' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Regex(pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', message: 'Bad Uuid'),
                    new Assert\Uuid(),
                ]),
                'active' => new Assert\Optional([
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'order' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Order is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThanOrEqual(1),
                    new Assert\LessThanOrEqual(3)
                ]),
                'dateFrom' => new Assert\Optional([
                    new Assert\NotBlank(message: 'DateFrom is empty'),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'dateTo' => new Assert\Optional([
                    new Assert\NotBlank(message: 'DateTo is empty'),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
            ],
        ]));
    }

    /**
     * @param string[] $searchData
     */
    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'userId', type: 'string', example: 'UUID', nullable: true),
        new OA\Property(property: 'active', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'dateFrom', type: 'datetime', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'dateTo', type: 'datetime', example: 'd.m.Y', nullable: true),
    ], type: 'object')]
    public function setSearchData(array $searchData): void
    {
        if (array_key_exists('userId', $searchData) && Uuid::isValid($searchData['userId'])) {
            $searchData['userId'] = Uuid::fromString($searchData['userId']);
        }

        if (array_key_exists('order', $searchData)) {
            if ($searchData['order'] !== TechnicalBreakOrder::LATEST->value && $searchData['order'] !== TechnicalBreakOrder::OLDEST->value && $searchData['order'] !== TechnicalBreakOrder::ACTIVE->value) {
                $searchData['order'] = TechnicalBreakOrder::ACTIVE->value;
            }
        }

        if (array_key_exists('dateFrom', $searchData)) {
            $searchData['dateFrom'] = DateTime::createFromFormat('d.m.Y', $searchData['dateFrom']);
        }
        if (array_key_exists('dateTo', $searchData)) {
            $searchData['dateTo'] = DateTime::createFromFormat('d.m.Y', $searchData['dateTo']);
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
    #[OA\Property(type: 'integer', example: 0)]
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
    #[OA\Property(type: 'integer', example: 10)]
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