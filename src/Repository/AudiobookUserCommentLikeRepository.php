<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AudiobookUserCommentLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AudiobookUserCommentLike>
 *
 * @method AudiobookUserCommentLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudiobookUserCommentLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudiobookUserCommentLike[]    findAll()
 * @method AudiobookUserCommentLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookUserCommentLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudiobookUserCommentLike::class);
    }

    /**
     * @param AudiobookUserCommentLike $entity
     * @param bool $flush
     * @return void
     */
    public function add(AudiobookUserCommentLike $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AudiobookUserCommentLike $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookUserCommentLike $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
