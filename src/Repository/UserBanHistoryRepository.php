<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBanHistory;
use DateTime;
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

    public function add(UserBanHistory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserBanHistory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getActiveBan(User $user): ?UserBanHistory
    {
        $today = new DateTime();

        return $this->createQueryBuilder('ub')
            ->where('ub.user = :userId')
            ->andWhere('ub.dateTo >= :today')
            ->setParameter('userId', $user->getId()->toBinary())
            ->setParameter('today', $today)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
