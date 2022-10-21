<?php

namespace App\Repository;

use App\Entity\AudiobookInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AudiobookInfo>
 *
 * @method AudiobookInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudiobookInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudiobookInfo[]    findAll()
 * @method AudiobookInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudiobookInfo::class);
    }

    /**
     * @param AudiobookInfo $entity
     * @param bool $flush
     * @return void
     */
    public function add(AudiobookInfo $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param AudiobookInfo $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookInfo $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return AudiobookInfo[] Returns an array of AudiobookInfo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AudiobookInfo
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
