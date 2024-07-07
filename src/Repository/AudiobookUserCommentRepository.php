<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AudiobookUserComment;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AudiobookUserComment>
 *
 * @method AudiobookUserComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudiobookUserComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudiobookUserComment[]    findAll()
 * @method AudiobookUserComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookUserCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudiobookUserComment::class);
    }

    /**
     * @param AudiobookUserComment $entity
     * @param bool $flush
     * @return void
     */
    public function add(AudiobookUserComment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AudiobookUserComment $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookUserComment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AudiobookUserComment[]
     */
    public function getUserLastCommentsByMinutes(User $user, string $minutes): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.deleted = false')
            ->andWhere('c.user = :user')
            ->andWhere('c.dateAdd >= :dateAdd')
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('dateAdd', (new DateTime())->modify('-' . $minutes . ' minutes'));

        return $qb->getQuery()->execute();
    }

    public function setLastUserLastCommentsByMinutesToDeleted(User $user, string $minutes): void
    {
        $qb = $this->createQueryBuilder('c');

        $qb->update()
            ->set('c.deleted', true)
            ->where('c.deleted = false')
            ->andWhere('c.user = :user')
            ->andWhere('c.dateAdd < :dateAdd')
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('dateAdd', (new DateTime())->modify('-' . $minutes . ' minutes'));

        $qb->getQuery()->execute();
    }
}
