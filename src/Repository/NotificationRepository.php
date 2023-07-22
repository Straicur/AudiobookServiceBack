<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Enums\NotificationOrderSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            ->orderBy("n.dateAdd", "DESC");

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserActiveNotifications(User $user): int
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.users', 'u')
            ->leftJoin('n.notificationChecks','nc')
            ->select('COUNT(nc.id) AS HIDDEN notifications', 'n')
            ->where('n.deleted = false')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->having("count(nc.id) = 0")
            ->orderBy('notifications', "DESC")
            ->groupBy('n');

        $query = $qb->getQuery();

        return count($query->execute());
    }
    /**
     * @param string|null $text
     * @param int|null $type
     * @param bool|null $deleted
     * @param int|null $order
     * @return Notification[]
     */
    public function getSearchNotifications(?string $text = null, ?int $type = null, ?bool $deleted = null, int $order = null): array
    {
        $qb = $this->createQueryBuilder('n');

        if ($text != null) {
            $qb->andWhere('n.metaData LIKE :text')
                ->setParameter('text', $text);
        }
        if ($type != null) {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $type);
        }
        if (is_bool($deleted)) {
            $qb->andWhere('n.deleted = :deleted')
                ->setParameter('deleted', $deleted);
        }
        if ($order != null) {
            switch ($order) {
                case NotificationOrderSearch::LATEST->value:
                {
                    $qb->orderBy("n.dateAdd", "DESC");
                    break;
                }
                case NotificationOrderSearch::OLDEST->value:
                {
                    $qb->orderBy("n.dateAdd", "ASC");
                    break;
                }
            }
        }

        $query = $qb->getQuery();

        return $query->execute();
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
