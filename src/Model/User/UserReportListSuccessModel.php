<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class UserReportListSuccessModel implements ModelInterface
{
    /**
     * @var UserReportModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: UserReportModel::class))
    )]
    private array $reports = [];

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @return UserReportModel[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @param UserReportModel[] $reports
     */
    public function setReports(array $reports): void
    {
        $this->reports = $reports;
    }

    public function addReport(UserReportModel $report): void
    {
        $this->reports[] = $report;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }
}
