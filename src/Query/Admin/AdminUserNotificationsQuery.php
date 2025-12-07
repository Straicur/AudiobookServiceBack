<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use App\Enums\NotificationOrderSearch;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class AdminUserNotificationsQuery
{
    #[Assert\NotNull(message: 'Page is null')]
    #[Assert\NotBlank(message: 'Page is empty')]
    #[Assert\Type(type: 'integer')]
    private int $page;

    #[Assert\NotNull(message: 'Limit is null')]
    #[Assert\NotBlank(message: 'Limit is empty')]
    #[Assert\Type(type: 'integer')]
    private int $limit;

    /**
     * @Assert\Collection(fields={})
     */
    protected array $searchData = [];

    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'text', type: 'string', example: 'text', nullable: true),
        new OA\Property(property: 'type', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'deleted', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (
            array_key_exists('order', $searchData)
            && $searchData['order'] !== NotificationOrderSearch::LATEST->value
            && $searchData['order'] !== NotificationOrderSearch::OLDEST->value
        ) {
            $searchData['order'] = NotificationOrderSearch::LATEST->value;
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
