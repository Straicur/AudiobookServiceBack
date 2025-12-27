<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\TechnicalBreak;
use App\Enums\TechnicalBreakOrder;
use App\Model\Serialization\AdminTechnicalBreaksSearchModel;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

use function count;

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

    public function add(TechnicalBreak $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

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
    public function getTechnicalBreakByPage(AdminTechnicalBreaksSearchModel $adminTechnicalBreaksSearchModel): array
    {
        $qb = $this->createQueryBuilder('tb');

        if ($adminTechnicalBreaksSearchModel->getNameOrLastname() !== null) {
            $qb->innerJoin('tb.user', 'u', Join::WITH, 'u.id = tb.user')
                ->innerJoin('u.userInformation', 'ui', Join::WITH, 'ui.user = u.id')
                ->andWhere('((ui.firstname LIKE :nameOrLastname) OR (ui.lastname LIKE :nameOrLastname) )')
                ->setParameter('nameOrLastname', '%' . $adminTechnicalBreaksSearchModel->getNameOrLastname() . '%');
        }

        if ($adminTechnicalBreaksSearchModel->getActive() !== null) {
            $qb->andWhere('tb.active = :active')
                ->setParameter('active', $adminTechnicalBreaksSearchModel->getActive());
        }

        if ($adminTechnicalBreaksSearchModel->getDateFrom() !== null && $adminTechnicalBreaksSearchModel->getDateTo() !== null) {
            $qb->andWhere('((tb.dateFrom > :dateFrom) AND (tb.dateFrom < :dateTo))')
                ->setParameter('dateFrom', $adminTechnicalBreaksSearchModel->getDateFrom())
                ->setParameter('dateTo', $adminTechnicalBreaksSearchModel->getDateTo());
        } elseif ($adminTechnicalBreaksSearchModel->getDateTo() !== null) {
            $qb->andWhere('(tb.dateFrom < :dateTo)')
                ->setParameter('dateTo', $adminTechnicalBreaksSearchModel->getDateTo());
        } elseif ($adminTechnicalBreaksSearchModel->getDateFrom() !== null) {
            $qb->andWhere('(tb.dateFrom > :dateFrom)')
                ->setParameter('dateFrom', $adminTechnicalBreaksSearchModel->getDateFrom());
        }

        if ($adminTechnicalBreaksSearchModel->getOrder() !== null) {
            switch ($adminTechnicalBreaksSearchModel->getOrder()) {
                case TechnicalBreakOrder::LATEST->value:
                    $qb->orderBy('tb.dateFrom', 'DESC');
                    break;
                case TechnicalBreakOrder::OLDEST->value:
                    $qb->orderBy('tb.dateFrom', 'ASC');
                    break;
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
