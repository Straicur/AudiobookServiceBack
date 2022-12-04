<?php

namespace App\Repository;

use App\Entity\AudiobookUserComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(AudiobookUserComment $entity, bool $flush = false): void
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
    public function remove(AudiobookUserComment $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return AudiobookUserComment[] Returns an array of AudiobookUserComment objects
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

//    public function findOneBySomeField($value): ?AudiobookUserComment
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
