<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminReportListSuccessModel implements ModelInterface
{
    /**
     * @var AdminReportModel[]
     */
    private array $reports = [];

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @return AdminReportModel[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @param AdminReportModel[] $reports
     */
    public function setReports(array $reports): void
    {
        $this->reports = $reports;
    }

    public function addReport(AdminReportModel $report): void
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
