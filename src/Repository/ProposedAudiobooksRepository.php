<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProposedAudiobooks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProposedAudiobooks>
 *
 * @method ProposedAudiobooks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProposedAudiobooks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProposedAudiobooks[]    findAll()
 * @method ProposedAudiobooks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposedAudiobooksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProposedAudiobooks::class);
    }

    /**
     * @param ProposedAudiobooks $entity
     * @param bool $flush
     * @return void
     */
    public function add(ProposedAudiobooks $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param ProposedAudiobooks $entity
     * @param bool $flush
     * @return void
     */
    public function remove(ProposedAudiobooks $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
