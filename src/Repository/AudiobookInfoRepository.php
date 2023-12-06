<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookInfo;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param AudiobookInfo $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookInfo $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param User $user
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

    /**
     * @param User $user
     * @param Audiobook $audiobook
     * @return void
     */
    public function deActiveAudiobookInfos(User $user, Audiobook $audiobook): void
    {
        $qb = $this->createQueryBuilder('ai');

        $qb->update()
            ->set("ai.active", ":status")
            ->andWhere('ai.user = :user')
            ->andWhere('ai.audiobook = :audiobook')
            ->andWhere('ai.active = true')
            ->setParameter("status", false)
            ->setParameter('user', $user->getId()->toBinary())
            ->setParameter('audiobook', $audiobook->getId()->toBinary());

        $query = $qb->getQuery();
        $query->execute();
    }
//    /**
//     * @return AudiobookInfo[] Returns an array of AudiobookInfo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AudiobookInfo
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
