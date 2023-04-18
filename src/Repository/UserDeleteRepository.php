<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserDelete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserDelete>
 *
 * @method UserDelete|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDelete|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDelete[]    findAll()
 * @method UserDelete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDeleteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDelete::class);
    }

    /**
     * @param UserDelete $entity
     * @param bool $flush
     * @return void
     */
    public function add(UserDelete $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param UserDelete $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserDelete $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function userInList(User $user): bool
    {
        $qb = $this->createQueryBuilder('ud');

        $qb->where('ud.user = :user')
            ->andWhere('((ud.deleted = true) OR (ud.dateDeleted IS NOT NULL))')
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        return count($query->execute()) > 0;
    }

    /**
     * @return UserDelete[]
     */
    public function getUsersToDelete(): array
    {
        $qb = $this->createQueryBuilder('ud');

        $qb->leftJoin('ud.user', 'u')
            ->where('ud.deleted = false')
            ->andWhere('ud.dateDeleted IS NULL')
            ->andWhere('u.active = false');

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function userInToDeleteList(User $user): bool
    {
        $qb = $this->createQueryBuilder('ud');

        $qb->where('ud.user = :user')
            ->andWhere('((ud.deleted = true) OR ((ud.dateDeleted IS NULL) AND (ud.declined = false)))')
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        return count($query->execute()) > 0;
    }
//    /**
//     * @return UserDelete[] Returns an array of UserDelete objects
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

//    public function findOneBySomeField($value): ?UserDelete
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
