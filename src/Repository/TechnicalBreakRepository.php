<?php

namespace App\Repository;

use App\Entity\TechnicalBreak;
use App\Enums\TechnicalBreakOrder;
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
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param TechnicalBreak $entity
     * @param bool $flush
     * @return void
     */
    public function remove(TechnicalBreak $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Uuid|null $userId
     * @param bool|null $active
     * @param int|null $order
     * @param \DateTime|null $dateFrom
     * @param \DateTime|null $dateTo
     * @return TechnicalBreak[]
     */
    public function getTechnicalBreakByPage(?Uuid $userId, ?bool $active, ?int $order, ?\DateTime $dateFrom, ?\DateTime $dateTo): array
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
        } else if ($dateTo != null) {
            $qb->andWhere('(tb.dateFrom < :dateTo)')
                ->setParameter('dateTo', $dateTo);
        } else if ($dateFrom != null) {
            $qb->andWhere('(tb.dateFrom > :dateFrom)')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($order !== null) {
            switch ($order) {
                case TechnicalBreakOrder::LATEST->value:
                {
                    $qb->orderBy("a.dateAdd", "DESC");
                    break;
                }
                case TechnicalBreakOrder::OLDEST->value:
                {
                    $qb->orderBy("a.dateAdd", "ASC");
                    break;
                }
                case TechnicalBreakOrder::ACTIVE->value:
                {
                    $qb->orderBy("tb.active", "DESC");
                    break;
                }
            }
        }

        return $qb->getQuery()->execute();
    }
//    /**
//     * @return TechnicalBreak[] Returns an array of TechnicalBreak objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TechnicalBreak
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
