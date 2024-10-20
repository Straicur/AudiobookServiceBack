<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Enums\NotificationOrderSearch;
use App\Model\Serialization\AdminNotificationsSearchModel;
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


    public function getNumberNotificationsFromLastWeek(): int
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
     * @return Notification[]
     */
    public function getUserNotifications(User $user): array
    {
        $qb = $this->createQueryBuilder('n')
            ->innerJoin('n.users', 'u', Join::WITH, 'u.id = :user')
            ->where('n.deleted = false')
            ->andWhere('n.active = true')
            ->setParameter('user', $user->getId()->toBinary())
            ->orderBy('n.dateAdd', 'DESC');

        return $qb->getQuery()->execute();
    }

    public function getUserActiveNotifications(User $user): int
    {
        $qb = $this->createQueryBuilder('n')
            ->innerJoin('n.users', 'u', Join::WITH, 'u.id = :user')
            ->leftJoin('n.notificationChecks', 'nc')
            ->select('COUNT(nc.id) AS HIDDEN notifications', 'n')
            ->where('n.deleted = false')
            ->andWhere('n.active = true')
            ->setParameter('user', $user->getId()->toBinary())
            ->having('count(nc.id) = 0')
            ->orderBy('notifications', 'DESC')
            ->groupBy('n');

        $query = $qb->getQuery();

        return count($query->execute());
    }

    /**
     * @return Notification[]
     */
    public function getSearchNotifications(AdminNotificationsSearchModel $adminNotificationsSearchModel): array
    {
        $qb = $this->createQueryBuilder('n');

        if ($adminNotificationsSearchModel->getText() !== null) {
            $qb->andWhere('n.metaData LIKE :text')
                ->setParameter('text', '%' . $adminNotificationsSearchModel->getText() . '%');
        }
        if ($adminNotificationsSearchModel->getType() !== null) {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $adminNotificationsSearchModel->getType());
        }
        if (is_bool($adminNotificationsSearchModel->getDeleted())) {
            $qb->andWhere('n.deleted = :deleted')
                ->setParameter('deleted', $adminNotificationsSearchModel->getDeleted());
        }

        switch ($adminNotificationsSearchModel->getOrder()) {
            case NotificationOrderSearch::OLDEST->value:
            {
                $qb->orderBy('n.dateAdd', 'ASC');
                break;
            }
            default:
            {
                $qb->orderBy('n.dateAdd', 'DESC');
                break;
            }
        }

        return $qb->getQuery()->execute();
    }

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

    /**
     * @return Notification[]
     */
    public function getNotificationsToActivate(): array
    {
        $today = new DateTime();

        $qb = $this->createQueryBuilder('n')
            ->where('n.deleted = false')
            ->andWhere('n.active = false')
            ->andWhere('n.dateActive <= :today')
            ->setParameter('today', $today)
            ->orderBy('n.dateAdd', 'DESC');

        return $qb->getQuery()->execute();
    }
}
