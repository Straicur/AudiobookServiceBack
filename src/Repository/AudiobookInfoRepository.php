<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookInfo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AudiobookInfo>
 *
 * @method AudiobookInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudiobookInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudiobookInfo[]    findAll()
 * @method AudiobookInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudiobookInfo::class);
    }

    /**
     * @param AudiobookInfo $entity
     * @param bool $flush
     * @return void
     */
    public function add(AudiobookInfo $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AudiobookInfo $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookInfo $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return AudiobookInfo[]
     */
    public function getActiveAudiobookInfos(User $user): array
    {
        $qb = $this->createQueryBuilder('ai')
            ->innerJoin('ai.audiobook', 'a', Join::WITH, 'a.active = true')
            ->innerJoin('ai.user', 'u', Join::WITH, 'u.id = :user')
            ->where('ai.active = true')
            ->setParameter('user', $user->getId()->toBinary());

        return $qb->getQuery()->execute();
    }

    public function deActiveAudiobookInfos(User $user, Audiobook $audiobook): void
    {
        $qb = $this->createQueryBuilder('ai');

        $qb->update()
            ->set('ai.active', ':status')
            ->andWhere('ai.user = :user')
            ->andWhere('ai.audiobook = :audiobook')
            ->andWhere('ai.active = true')
            ->setParameter('status', false)
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('audiobook', $audiobook->getId()->toBinary());

        $query = $qb->getQuery();
        $query->execute();
    }

    public function getUsersWhereAudiobookInAudiobookInfo(Audiobook $audiobook): array
    {
        $audiobookCategories = [];
        foreach ($audiobook->getCategories() as $category) {
            $audiobookCategories[] = $category->getId()->toBinary();
        }

        $qb = $this->createQueryBuilder('ai');

        $qb->select('u.id')
            ->distinct()
            ->innerJoin('ai.user', 'u', Join::WITH, 'u.id = ai.user and u.banned = false and u.active = true')
            ->innerJoin('ai.audiobook', 'a')
            ->innerJoin('a.categories', 'c', Join::WITH, 'c.id IN (:categories)')
            ->setParameter('categories', $audiobookCategories);

        $results = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        foreach ($results as &$result) {
            $result = $result['id'];
        }

        return $results;
    }
}
