<?php

namespace App\Repository;

use App\Entity\TechnicalBreak;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TechnicalBreak>
 *
 * @method TechnicalBreak|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnicalBreak|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnicalBreak[]    findAll()
 * @method TechnicalBreak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicalBreakRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TechnicalBreak::class);
    }

    /**
     * @param TechnicalBreak $entity
     * @param bool $flush
     * @return void
     */
    public function add(TechnicalBreak $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param TechnicalBreak $entity
     * @param bool $flush
     * @return void
     */
    public function remove(TechnicalBreak $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return TechnicalBreak[] Returns an array of TechnicalBreak objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TechnicalBreak
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
