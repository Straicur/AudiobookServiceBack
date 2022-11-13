<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param User $entity
     * @param bool $flush
     * @return void
     */
    public function add(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $entity
     * @param bool $flush
     * @return void
     */
    public function remove(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Role $role
     * @return User[]
     */
    public function getUsersByRole(Role $role): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.roles', 'r')
            ->where('r.id = :role')
            ->andWhere('u.banned = false')
            ->setParameter('role', $role->getId()->toBinary());

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function userHasRole(User $user, Role $role): bool
    {
        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.roles', 'r')
            ->where('u.id = :user')
            ->andWhere('r.id = :role')
            ->andWhere('u.banned = false')
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('role', $role->getId()->toBinary());

        $query = $qb->getQuery();

        return count($query->execute()) > 0;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function userIsAdmin(User $user): bool
    {
        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.roles', 'r')
            ->where('u.id = :user')
            ->andWhere('r.name = :role')
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('role', "Administrator");

        $query = $qb->getQuery();

        return count($query->execute()) > 0;
    }

    /**
     * @param Audiobook $audiobook
     * @return User[]
     */
    public function getUsersWhereAudiobookInProposed(Audiobook $audiobook): array
    {
        $audiobookCategories = [];
        foreach ($audiobook->getCategories() as $category) {
            $audiobookCategories[] = $category->getId()->toBinary();
        }

        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.proposedAudiobooks', 'pa')
            ->leftJoin('pa.audiobooks', 'a')
            ->leftJoin('a.categories', 'c')
            ->where('c.id IN (:categories)')
            ->andWhere('u.banned = false')
            ->andWhere('u.active = true')
            ->setParameter('categories', $audiobookCategories);

        $query = $qb->getQuery();

        return $query->execute();
    }
//    /**
//     * @return User[] Returns an array of User objects
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

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
