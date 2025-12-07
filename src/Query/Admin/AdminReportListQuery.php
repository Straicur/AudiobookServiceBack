<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use App\Enums\ReportOrderSearch;
use App\Enums\ReportType;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class AdminReportListQuery
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
        new OA\Property(property: 'actionId', type: 'string', example: 'UUID', nullable: true),
        new OA\Property(property: 'description', type: 'string', example: 'description', nullable: true),
        new OA\Property(property: 'email', type: 'string', example: 'fdas@gmail.com', nullable: true),
        new OA\Property(property: 'ip', type: 'string', example: '192.021.32', nullable: true),
        new OA\Property(property: 'type', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'user', type: 'string', example: true, nullable: true),
        new OA\Property(property: 'accepted', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'denied', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'dateFrom', type: 'string', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'dateTo', type: 'string', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (
            array_key_exists('type', $searchData)
            && $searchData['type'] !== ReportType::COMMENT->value
            && $searchData['type'] !== ReportType::AUDIOBOOK_PROBLEM->value
            && $searchData['type'] !== ReportType::CATEGORY_PROBLEM->value
            && $searchData['type'] !== ReportType::SYSTEM_PROBLEM->value
            && $searchData['type'] !== ReportType::USER_PROBLEM->value
            && $searchData['type'] !== ReportType::SETTINGS_PROBLEM->value
            && $searchData['type'] !== ReportType::RECRUITMENT_REQUEST->value
            && $searchData['type'] !== ReportType::OTHER->value
        ) {
            $searchData['type'] = ReportType::OTHER->value;
        }

        if (
            array_key_exists('order', $searchData)
            && $searchData['order'] !== ReportOrderSearch::OLDEST->value
            && $searchData['order'] !== ReportOrderSearch::LATEST->value
        ) {
            $searchData['order'] = ReportOrderSearch::LATEST->value;
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
