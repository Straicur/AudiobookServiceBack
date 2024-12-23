<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\MyList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MyList>
 *
 * @method MyList|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyList|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyList[]    findAll()
 * @method MyList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MyListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyList::class);
    }

    /**
     * @param MyList $entity
     * @param bool $flush
     * @return void
     */
    public function add(MyList $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param MyList $entity
     * @param bool $flush
     * @return void
     */
    public function remove(MyList $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAudiobookInMyList(User $user, Audiobook $audiobook): bool
    {
        $qb = $this->createQueryBuilder('ml')
            ->innerJoin('ml.audiobooks', 'a', Join::WITH, 'a.id = :audiobook and a.active = true')
            ->where('ml.user = :user')
            ->setParameter('audiobook', $audiobook->getId()->toBinary())
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        $result = $query->execute();

        return count($result) > 0;
    }
}
