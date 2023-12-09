<?php

namespace App\Repository;

use App\Entity\UserInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserInformation>
 *
 * @method UserInformation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInformation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInformation[]    findAll()
 * @method UserInformation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserInformationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInformation::class);
    }

    /**
     * @param UserInformation $entity
     * @param bool $flush
     * @return void
     */
    public function add(UserInformation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserInformation $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserInformation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UserInformation[] Returns an array of UserInformation objects
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

//    public function findOneBySomeField($value): ?UserInformation
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
