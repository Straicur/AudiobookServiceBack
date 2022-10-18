<?php

namespace App\Repository;

use App\Entity\MyList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MyList>
 *
 * @method MyList|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyList|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyList[]    findAll()
 * @method MyList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyList::class);
    }

    /**
     * @param MyList $entity
     * @param bool $flush
     * @return void
     */
    public function add(MyList $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param MyList $entity
     * @param bool $flush
     * @return void
     */
    public function remove(MyList $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return MyList[] Returns an array of MyList objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MyList
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
