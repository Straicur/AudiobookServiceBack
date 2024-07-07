<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserDelete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserDelete $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserDelete $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
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

        $qb->innerJoin('ud.user', 'u', Join::WITH, 'u.active = false')
            ->where('ud.deleted = false')
            ->andWhere('ud.dateDeleted IS NULL');

        return $qb->getQuery()->execute();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function userInToDeleteList(User $user): bool
    {
        $qb = $this->createQueryBuilder('ud');

        $qb->where('ud.user = :user')
            ->andWhere('((ud.deleted = true) AND (ud.dateDeleted IS NOT NULL))')
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        return count($query->execute()) > 0;
    }
}
