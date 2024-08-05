<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminTechnicalCachePoolsModel implements ModelInterface
{
    private array $adminCachePools = [];
    private array $userCachePools = [];

    /**
     * @return CacheModel[]
     */
    public function getAdminCachePools(): array
    {
        return $this->adminCachePools;
    }

    public function setAdminCachePools(array $adminCachePools): void
    {
        $this->adminCachePools = $adminCachePools;
    }

    public function addAdminCachePool(CacheModel $adminCachePool): void
    {
        $this->adminCachePools[] = $adminCachePool;
    }

    /**
     * @return CacheModel[]
     */
    public function getUserCachePools(): array
    {
        return $this->userCachePools;
    }

    public function setUserCachePools(array $userCachePools): void
    {
        $this->userCachePools = $userCachePools;
    }

    public function addUserCachePool(CacheModel $userCachePool): void
    {
        $this->userCachePools[] = $userCachePool;
    }
}
