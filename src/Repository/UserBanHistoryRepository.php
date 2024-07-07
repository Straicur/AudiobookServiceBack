<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserBanHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBanHistory>
 *
 * @method UserBanHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBanHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBanHistory[]    findAll()
 * @method UserBanHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBanHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBanHistory::class);
    }

    /**
     * @param UserBanHistory $entity
     * @param bool $flush
     * @return void
     */
    public function add(UserBanHistory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserBanHistory $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserBanHistory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
