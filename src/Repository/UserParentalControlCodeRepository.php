<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserParentalControlCode;
use DateTime;
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
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserParentalControlCode $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserParentalControlCode $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserParentalControlCodeFromLastWeakByUser(User $user): int
    {
        $today = new DateTime();
        $lastDate = clone $today;
        $lastDate->modify('-7 day');

        $qb = $this->createQueryBuilder('upcc')
            ->where('( :dateFrom <= upcc.dateAdd AND :dateTo >= upcc.dateAdd)')
            ->andWhere('upcc.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
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
            ->set('upcc.active', 'false')
            ->where('upcc.user = :user')
            ->setParameter('user', $user->getId()->toBinary());

        $qb->getQuery()->execute();
    }
}
