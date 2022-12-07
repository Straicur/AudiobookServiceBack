<?php

namespace App\Repository;

use App\Entity\AudiobookUserCommentLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(AudiobookUserCommentLike $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(AudiobookUserCommentLike $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return AudiobookUserCommentLike[] Returns an array of AudiobookUserCommentLike objects
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

//    public function findOneBySomeField($value): ?AudiobookUserCommentLike
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
