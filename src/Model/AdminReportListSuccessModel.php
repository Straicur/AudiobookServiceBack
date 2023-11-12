<?php

namespace App\Model;

class AdminReportListSuccessModel implements ModelInterface
{
    /**
     * @var ReportModel[]
     */
    private array $reports = [];

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @return ReportModel[]
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @param ReportModel[] $reports
     */
    public function setReports(array $reports): void
    {
        $this->reports = $reports;
    }

    public function addReport(ReportModel $report): void
    {
        $this->reports[] = $report;
    }

    /**
     * @return int
     */
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

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    /**
     * @param int $maxPage
     */
    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }

}