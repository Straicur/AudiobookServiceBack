<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\MyList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    /**
     * @param User $user
     * @param Audiobook $audiobook
     * @return bool
     */
    public function getAudiobookInMyList(User $user, Audiobook $audiobook): bool
    {
        $qb = $this->createQueryBuilder('ml')
            ->innerJoin('ml.audiobooks', 'a', Join::WITH, 'a.id = :audiobook')
            ->where('a.active = true')
            ->andWhere('ml.user = :user')
            ->setParameter('audiobook', $audiobook->getId()->toBinary())
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        $result = $query->execute();

        return count($result) > 0;
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
