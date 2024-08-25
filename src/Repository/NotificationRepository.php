<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Enums\NotificationOrderSearch;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Notification $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Notification $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function getNumberNotificationsFromLastWeak(): int
    {
        $today = new DateTime();
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
            ->innerJoin('n.users', 'u', Join::WITH, 'u.id = :user')
            ->where('n.deleted = false')
            ->setParameter('user', $user->getId()->toBinary())
            ->orderBy('n.dateAdd', 'DESC');

        return $qb->getQuery()->execute();
    }

    /**
     * @param User $user
     * @return int
     */
    public function getUserActiveNotifications(User $user): int
    {
        $qb = $this->createQueryBuilder('n')
            ->innerJoin('n.users', 'u', Join::WITH, 'u.id = :user')
            ->leftJoin('n.notificationChecks', 'nc')
            ->select('COUNT(nc.id) AS HIDDEN notifications', 'n')
            ->where('n.deleted = false')
            ->setParameter('user', $user->getId()->toBinary())
            ->having('count(nc.id) = 0')
            ->orderBy('notifications', 'DESC')
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

        if ($text !== null) {
            $qb->andWhere('n.metaData LIKE :text')
                ->setParameter('text', '%' . $text . '%');
        }
        if ($type !== null) {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $type);
        }
        if (is_bool($deleted)) {
            $qb->andWhere('n.deleted = :deleted')
                ->setParameter('deleted', $deleted);
        }

        switch ($order) {
            case NotificationOrderSearch::OLDEST->value:
            {
                $qb->orderBy('n.dateAdd', 'ASC');
                break;
            }
            case NotificationOrderSearch::LATEST->value:
            default:
            {
                $qb->orderBy('n.dateAdd', 'DESC');
                break;
            }
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param Uuid $actionId
     * @return void
     */
    public function updateDeleteNotificationsByAction(Uuid $actionId): void
    {
        $qb = $this->createQueryBuilder('n')
            ->update()
            ->set('n.deleted', 'true')
            ->set('n.dateDeleted', ':dateDeleted')
            ->where('n.actionId = :actionId')
            ->andWhere('n.deleted = :deletedStatus')
            ->setParameter('deletedStatus', false)
            ->setParameter('dateDeleted', new DateTime())
            ->setParameter('actionId', $actionId->toBinary());

        $qb->getQuery()->execute();
    }
}
