<?php

namespace App\Repository;

use App\Entity\Audiobook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Audiobook>
 *
 * @method Audiobook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Audiobook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Audiobook[]    findAll()
 * @method Audiobook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Audiobook::class);
    }

    /**
     * @param Audiobook $entity
     * @param bool $flush
     * @return void
     */
    public function add(Audiobook $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Audiobook $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Audiobook $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return Audiobook[] Returns an array of Audiobook objects
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

//    public function findOneBySomeField($value): ?Audiobook
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
