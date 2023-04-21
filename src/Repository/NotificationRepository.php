<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @param Notification $entity
     * @param bool $flush
     * @return void
     */
    public function add(Notification $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Notification $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Notification $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return int
     */
    public function getNotificationsFromLastWeak(): int
    {
        $today = new \DateTime('NOW');
        $lastDate = clone $today;
        $lastDate->modify('-7 day');

        $qb = $this->createQueryBuilder('n')
            ->where('( :dateFrom <= n.dateAdd AND :dateTo >= n.dateAdd)')
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate);

        $query = $qb->getQuery();

        $result = $query->execute();

        return count($result);
    }

    /**
     * @param User $user
     * @return Notification[]
     */
    public function getUserNotifications(User $user): array
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.users', 'u')
            ->where('n.deleted = false')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->orderBy("n.dateAdd", "DESC")
        ;

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param Uuid $notification
     * @param User $user
     * @return ?Notification
     */
    public function getUserNotification(Uuid $notification, User $user): ?Notification
    {

        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.users', 'u')
            ->where('n.id = :notification')
            ->andWhere('n.deleted = false')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('notification', $notification->toBinary());

        $query = $qb->getQuery();

        $res = $query->execute();

        return count($res) > 0 ? $res[0] : null;
    }
//    /**
//     * @return Notification[] Returns an array of Notification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Notification
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
