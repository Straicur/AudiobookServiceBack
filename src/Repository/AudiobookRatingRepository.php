<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AudiobookRating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AudiobookRating>
 *
 * @method AudiobookRating|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudiobookRating|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudiobookRating[]    findAll()
 * @method AudiobookRating[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudiobookRating::class);
    }

    /**
     * @param AudiobookRating $entity
     * @param bool $flush
     * @return void
     */
    public function add(AudiobookRating $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param AudiobookRating $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookRating $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
