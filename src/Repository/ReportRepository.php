<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report;
use App\Entity\User;
use App\Enums\ReportOrderSearch;
use App\Enums\ReportType;
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
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Report $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Report $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function notLoggedUserReportsCount(string $ip, string $email, ReportType $type): array
    {
        $today = new DateTime();
        $lastDate = clone $today;
        $lastDate->modify('-2 day');

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('((r.ip = :ip) or (r.email = :email)) ')
            ->andWhere('( :dateFrom <= r.dateAdd AND :dateTo >= r.dateAdd)')
            ->andWhere('(r.type  = :type)')
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate)
            ->setParameter('email', $email)
            ->setParameter('type', $type->value)
            ->setParameter('ip', $ip);

        return current($qb->getQuery()->execute());
    }

    public function loggedUserReportsCount(User $user, ReportType $type, ?string $actionId = null): array
    {
        $today = new DateTime();
        $lastDate = clone $today;
        $lastDate->modify('-2 day');

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('((r.user IS NOT NULL) AND (r.user = :user))')
            ->andWhere('(r.type  = :type)')
            ->andWhere('( :dateFrom <= r.dateAdd AND :dateTo >= r.dateAdd)');

        if (($type === ReportType::COMMENT || $type === ReportType::AUDIOBOOK_PROBLEM || $type === ReportType::CATEGORY_PROBLEM) && $actionId !== null) {
            $qb->andWhere('r.actionId LIKE :actionId')
                ->setParameter('actionId', '%' . $actionId . '%');
        }

        $qb->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate)
            ->setParameter('type', $type->value)
            ->setParameter('user', $user->getId()->toBinary());

        return current($qb->getQuery()->execute());
    }

    /**
     * @return Report[]
     */
    public function getReportsByPage(
        string $actionId = null,
        string $desc = null,
        string $email = null,
        string $ip = null,
        int $type = null,
        bool $user = null,
        bool $accepted = null,
        bool $denied = null,
        DateTime $dateFrom = null,
        DateTime $dateTo = null,
        int $order = null
    ): array {
        $qb = $this->createQueryBuilder('r');

        if ($actionId !== null) {
            $qb->andWhere('r.actionId LIKE :actionId')
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
        }

        if ($user !== null && !$user) {
            $qb->andWhere('r.user IS NULL');
        }

        if ($accepted) {
            $qb->andWhere('r.accepted = :accepted')
                ->setParameter('accepted', $accepted);
        }

        if ($denied) {
            $qb->andWhere('r.denied = :denied')
                ->setParameter('denied', $denied);
        }

        if ($dateFrom !== null && $dateTo !== null) {
            $qb->andWhere('((r.dateAdd >= :dateFrom) AND (r.dateAdd <= :dateTo))')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo);
        } elseif ($dateTo !== null) {
            $qb->andWhere('(r.dateAdd <= :dateTo)')
                ->setParameter('dateTo', $dateTo);
        } elseif ($dateFrom !== null) {
            $qb->andWhere('(r.dateAdd >= :dateFrom)')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($order !== null && $order === ReportOrderSearch::OLDEST->value) {
            $qb->orderBy('r.dateAdd', 'ASC');
        } else {
            $qb->orderBy('r.dateAdd', 'DESC');
        }

        return $qb->getQuery()->execute();
    }

    public function getSimilarReportsCount(string $actionId): ?array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.actionId LIKE :actionId')
            ->andWhere('r.accepted = false')
            ->andWhere('r.denied = false')
            ->setParameter('actionId', $actionId);

        return current($qb->getQuery()->execute());
    }
}
