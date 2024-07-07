<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report;
use App\Entity\User;
use App\Enums\ReportOrderSearch;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Report>
 *
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * @param Report $entity
     * @param bool $flush
     * @return void
     */
    public function add(Report $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Report $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Report $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function notLoggedUserReportsCount(string $ip)
    {
        $today = new DateTime();
        $lastDate = clone $today;
        $lastDate->modify('-2 day');

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.ip = :ip')
            ->andWhere('( :dateFrom <= r.dateAdd AND :dateTo >= r.dateAdd)')
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate)
            ->setParameter('ip', $ip);

        return $qb->getQuery()->execute()[0];
    }

    public function loggedUserReportsCount(User $user)
    {
        $today = new DateTime();
        $lastDate = clone $today;
        $lastDate->modify('-2 day');

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('((r.user IS NOT NULL) AND (r.user = :user))')
            ->andWhere('( :dateFrom <= r.dateAdd AND :dateTo >= r.dateAdd)')
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate)
            ->setParameter('user', $user->getId()->toBinary());

        return $qb->getQuery()->execute()[0];
    }

    /**
     * @param string|null $actionId
     * @param string|null $desc
     * @param string|null $email
     * @param string|null $ip
     * @param int|null $type
     * @param bool|null $user
     * @param bool|null $accepted
     * @param bool|null $denied
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @param int|null $order
     * @return Report[]
     */
    public function getReportsByPage(string $actionId = null, string $desc = null, string $email = null, string $ip = null, int $type = null, bool $user = null, bool $accepted = null, bool $denied = null, DateTime $dateFrom = null, DateTime $dateTo = null, int $order = null): array
    {
        $qb = $this->createQueryBuilder('r');

        if ($actionId !== null) {
            $qb->andWhere('r.actionId = :actionId')
                ->setParameter('actionId', $actionId);
        }

        if ($desc !== null) {
            $qb->andWhere('r.description LIKE :desc')
                ->setParameter('desc', $desc);
        }
        if ($email !== null) {
            $qb->andWhere('r.email LIKE :email')
                ->setParameter('email', $email);
        }
        if ($ip !== null) {
            $qb->andWhere('r.ip LIKE :ip')
                ->setParameter('ip', $ip);
        }

        if ($type !== null) {
            $qb->andWhere('r.type = :type')
                ->setParameter('type', $type);
        }

        if ($user) {
            $qb->andWhere('r.user IS NOT NULL');
        } else {
            $qb->andWhere('r.user IS NULL');
        }

        if ($accepted !== null) {
            $qb->andWhere('r.accepted = :accepted')
                ->setParameter('accepted', $accepted);
        }

        if ($denied !== null) {
            $qb->andWhere('r.type = :denied')
                ->setParameter('denied', $denied);
        }

        if ($dateFrom !== null && $dateTo !== null) {
            $qb->andWhere('((r.dateAdd > :dateFrom) AND (r.dateAdd < :dateTo))')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo);
        } elseif ($dateTo !== null) {
            $qb->andWhere('(r.dateAdd < :dateTo)')
                ->setParameter('dateTo', $dateTo);
        } elseif ($dateFrom !== null) {
            $qb->andWhere('(r.dateAdd > :dateFrom)')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($order !== null) {
            switch ($order) {
                case ReportOrderSearch::LATEST->value:
                {
                    $qb->orderBy('r.dateAdd', 'DESC');
                    break;
                }
                case ReportOrderSearch::OLDEST->value:
                {
                    $qb->orderBy('r.dateAdd', 'ASC');
                    break;
                }
            }
        }

        return $qb->getQuery()->execute();
    }
}
