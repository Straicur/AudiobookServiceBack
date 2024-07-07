<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NotificationCheck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationCheck>
 *
 * @method NotificationCheck|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationCheck|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationCheck[]    findAll()
 * @method NotificationCheck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationCheckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationCheck::class);
    }

    /**
     * @param NotificationCheck $entity
     * @param bool $flush
     * @return void
     */
    public function add(NotificationCheck $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param NotificationCheck $entity
     * @param bool $flush
     * @return void
     */
    public function remove(NotificationCheck $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
