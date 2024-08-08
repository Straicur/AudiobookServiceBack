<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TechnicalBreak;
use App\Enums\TechnicalBreakOrder;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<TechnicalBreak>
 *
 * @method TechnicalBreak|null find($id, $lockMode = null, $lockVersion = null)
 * @method TechnicalBreak|null findOneBy(array $criteria, array $orderBy = null)
 * @method TechnicalBreak[]    findAll()
 * @method TechnicalBreak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnicalBreakRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TechnicalBreak::class);
    }

    /**
     * @param TechnicalBreak $entity
     * @param bool $flush
     * @return void
     */
    public function add(TechnicalBreak $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param TechnicalBreak $entity
     * @param bool $flush
     * @return void
     */
    public function remove(TechnicalBreak $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Uuid|null $userId
     * @param bool|null $active
     * @param int|null $order
     * @param DateTime|null $dateFrom
     * @param DateTime|null $dateTo
     * @return TechnicalBreak[]
     */
    public function getTechnicalBreakByPage(?Uuid $userId, ?bool $active, ?int $order, ?DateTime $dateFrom, ?DateTime $dateTo): array
    {
        $qb = $this->createQueryBuilder('tb');

        if ($userId !== null) {
            $qb->andWhere('tb.user = :userId')
                ->setParameter('userId', $userId->toBinary());
        }

        if ($active !== null) {
            $qb->andWhere('tb.active = :active')
                ->setParameter('active', $active);
        }

        if ($dateFrom !== null && $dateTo !== null) {
            $qb->andWhere('((tb.dateFrom > :dateFrom) AND (tb.dateFrom < :dateTo))')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo);
        } elseif ($dateTo !== null) {
            $qb->andWhere('(tb.dateFrom < :dateTo)')
                ->setParameter('dateTo', $dateTo);
        } elseif ($dateFrom !== null) {
            $qb->andWhere('(tb.dateFrom > :dateFrom)')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($order !== null) {
            switch ($order) {
                case TechnicalBreakOrder::LATEST->value:
                {
                    $qb->orderBy('tb.dateFrom', 'DESC');
                    break;
                }
                case TechnicalBreakOrder::OLDEST->value:
                {
                    $qb->orderBy('tb.dateFrom', 'ASC');
                    break;
                }
                case TechnicalBreakOrder::ACTIVE->value:
                {
                    $qb->orderBy('tb.active', 'DESC');
                    break;
                }
            }
        } else {
            $qb->orderBy('tb.dateFrom', 'DESC');
        }

        return $qb->getQuery()->execute();
    }


    public function getNumberTechnicalBreakFromLastWeak(): int
    {
        $today = new DateTime();
        $lastDate = clone $today;
        $lastDate->modify('-7 day');

        $qb = $this->createQueryBuilder('tb')
            ->where('( :dateFrom <= tb.dateFrom AND :dateTo >= tb.dateFrom)')
            ->setParameter('dateTo', $today)
            ->setParameter('dateFrom', $lastDate);

        return count($qb->getQuery()->execute());
    }
}
