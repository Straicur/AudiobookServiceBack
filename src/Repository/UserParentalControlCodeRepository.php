<?php

namespace App\Repository;

use App\Entity\User;
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

    /**
     * @param User $user
     * @return int
     */
    public function getUserParentalControlCodeFromLastWeakByUser(User $user): int
    {
        $today = new \DateTime('NOW');
        $lastDate = clone $today;
        $lastDate->modify('-7 day');

        $qb = $this->createQueryBuilder('upcc')
            ->where('( :dateFrom <= upcc.dateAdd AND :dateTo >= upcc.dateAdd)')
            ->andWhere("upcc.user = :user")
            ->setParameter("user", $user->getId()->toBinary())
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate);

        return count($qb->getQuery()->execute());
    }

    /**
     * @param User $user
     * @return void
     */
    public function setCodesToNotActive(User $user): void
    {
        $qb = $this->createQueryBuilder('upcc')
            ->update()
            ->set("upcc.active", "false")
            ->where("upcc.user = :user")
            ->setParameter("user", $user->getId()->toBinary());

        $qb->getQuery()->execute();
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
