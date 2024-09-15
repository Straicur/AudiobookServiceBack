<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TechnicalBreak;
use App\Enums\TechnicalBreakOrder;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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
     * @return TechnicalBreak[]
     */
    public function getTechnicalBreakByPage(?string $nameOrLastname, ?bool $active, ?int $order, ?DateTime $dateFrom, ?DateTime $dateTo): array
    {
        $qb = $this->createQueryBuilder('tb');

        if ($nameOrLastname !== null) {
            $qb->innerJoin('tb.user', 'u', Join::WITH, 'u.id = tb.user')
                ->innerJoin('u.userInformation', 'ui', Join::WITH, 'ui.user = u.id')
                ->andWhere('((ui.firstname LIKE :nameOrLastname) OR (ui.lastname LIKE :nameOrLastname) )')
                ->setParameter('nameOrLastname', '%' . $nameOrLastname. '%');
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
