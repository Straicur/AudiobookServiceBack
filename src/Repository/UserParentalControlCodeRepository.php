<?php

namespace App\Repository;

use App\Entity\UserParentalControlCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserParentalControlCode>
 *
 * @method UserParentalControlCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserParentalControlCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserParentalControlCode[]    findAll()
 * @method UserParentalControlCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserParentalControlCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserParentalControlCode::class);
    }

    /**
     * @param UserParentalControlCode $entity
     * @param bool $flush
     * @return void
     */
    public function add(UserParentalControlCode $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param UserParentalControlCode $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserParentalControlCode $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return UserParentalControlCode[] Returns an array of UserParentalControlCode objects
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

//    public function findOneBySomeField($value): ?UserParentalControlCode
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
