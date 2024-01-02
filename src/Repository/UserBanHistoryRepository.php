<?php

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
    public function add(UserBanHistory $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param UserBanHistory $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserBanHistory $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return UserBanHistory[] Returns an array of UserBanHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserBanHistory
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
