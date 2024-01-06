<?php

namespace App\Model\Admin;

use App\Model\Error\ModelInterface;

class AdminTechnicalBreakSuccessModel implements ModelInterface
{
    public int $page;
    public int $limit;
    public int $maxPage;

    /**
     * @var AdminTechnicalBreakModel[]
     */
    private array $technicalBreaks = [];

    /**
     * @return AdminTechnicalBreakModel[]
     */
    public function getTechnicalBreaks(): array
    {
        return $this->technicalBreaks;
    }

    /**
     * @param AdminTechnicalBreakModel[] $technicalBreaks
     */
    public function setTechnicalBreaks(array $technicalBreaks): void
    {
        $this->technicalBreaks = $technicalBreaks;
    }

    public function addTechnicalBreak(AdminTechnicalBreakModel $technicalBreak): void
    {
        $this->technicalBreaks[] = $technicalBreak;
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