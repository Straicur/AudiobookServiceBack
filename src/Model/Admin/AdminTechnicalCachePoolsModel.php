<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminTechnicalCachePoolsModel implements ModelInterface
{
    private array $cachePools = [];

    /**
     * @return CacheModel[]
     */
    public function getCachePools(): array
    {
        return $this->cachePools;
    }

    /**
     * @param array $cachePools
     */
    public function setCachePools(array $cachePools): void
    {
        $this->cachePools = $cachePools;
    }

    public function addCachePool(CacheModel $cachePool)
    {
        $this->cachePools[] = $cachePool;
    }
}