<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\Role;
use App\Entity\User;
use App\Enums\UserOrderSearch;
use DateTime;
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

    /**
     * @return bool
     */
    public function newUsersFromLastWeak(): bool
    {
        $today = new \DateTime('NOW');
        $lastDate = clone $today;
        $lastDate->modify('-7 day');

        $qb = $this->createQueryBuilder('u')
            ->where('( :dateFrom <= u.dateCreate AND :dateTo >= u.dateCreate)')
            ->andWhere("u.active = true")
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate);

        $query = $qb->getQuery();

        $result = $query->execute();

        return count($result);
    }

    /**
     * @param string|null $email
     * @param string|null $phoneNumber
     * @param string|null $firstname
     * @param string|null $lastname
     * @param bool|null $active
     * @param bool|null $banned
     * @param int|null $order
     * @return User[]
     */
    public function searchUsers(string $email = null, string $phoneNumber = null, string $firstname = null, string $lastname = null, bool $active = null, bool $banned = null, int $order = null): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin('u.userInformation', 'ui');

        if ($email != null) {
            $qb->andWhere('ui.email LIKE :email')
                ->setParameter('email', $email);
        }
        if ($phoneNumber != null) {
            $qb->andWhere('ui.phoneNumber LIKE :phoneNumber')
                ->setParameter('phoneNumber', $phoneNumber);
        }
        if ($firstname != null) {
            $qb->andWhere('ui.firstname LIKE :firstname')
                ->setParameter('firstname', $firstname);
        }
        if ($lastname != null) {
            $qb->andWhere('ui.lastname LIKE :lastname')
                ->setParameter('lastname', $lastname);
        }
        if ($active != null) {
            $qb->andWhere('u.active = :active')
                ->setParameter('active', $active);
        }
        if ($banned != null) {
            $qb->andWhere('u.banned = :banned')
                ->setParameter('banned', $banned);
        }
        if ($order != null) {
            switch ($order) {
                case UserOrderSearch::LATEST->value: {
                        $qb->orderBy("u.dateCreate", "DESC");
                        break;
                    }
                case UserOrderSearch::OLDEST->value: {
                        $qb->orderBy("u.dateCreate", "ASC");
                        break;
                    }
                case UserOrderSearch::ALPHABETICAL_ASC->value: {
                        $qb->orderBy("ui.email", "ASC");
                        break;
                    }
                case UserOrderSearch::ALPHABETICAL_DESC->value: {
                        $qb->orderBy("ui.email", "DESC");
                        break;
                    }
            }
        }

        $query = $qb->getQuery();

        return $query->execute();
    }


    /**
     * @return void
     */
    public function bannedUsers(): void
    {
        $qb = $this->createQueryBuilder('u');

        $today = new DateTime('Now');

        $qb->update()
            ->set("u.banned", "false")
            ->where('u.banned = true')
            ->andWhere('u.bannedTo < :date')
            ->setParameter('date', $today);

        $qb->getQuery()->execute();
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
