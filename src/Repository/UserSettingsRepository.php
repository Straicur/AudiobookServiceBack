<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSettings>
 *
 * @method UserSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSettings[]    findAll()
 * @method UserSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSettings::class);
    }

    /**
     * @param UserSettings $entity
     * @param bool $flush
     * @return void
     */
    public function add(UserSettings $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserSettings $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserSettings $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
