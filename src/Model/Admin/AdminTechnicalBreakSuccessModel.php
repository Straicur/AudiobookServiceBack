<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class AdminTechnicalBreakSuccessModel implements ModelInterface
{
    public int $page;

    public int $limit;

    public int $maxPage;

    /**
     * @var AdminTechnicalBreakModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AdminTechnicalBreakModel::class))
    )]
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
