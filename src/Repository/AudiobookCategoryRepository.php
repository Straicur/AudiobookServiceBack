<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AudiobookCategory>
 *
 * @method AudiobookCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method AudiobookCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method AudiobookCategory[]    findAll()
 * @method AudiobookCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudiobookCategory::class);
    }

    /**
     * @param AudiobookCategory $entity
     * @param bool $flush
     * @return void
     */
    public function add(AudiobookCategory $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param AudiobookCategory $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookCategory $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Audiobook $audiobook
     * @return AudiobookCategory[]
     */
    public function getAudiobookCategories(Audiobook $audiobook): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->leftJoin('ac.audiobooks', 'a')
            ->where('a.id = :audiobook')
            ->setParameter('audiobook', $audiobook->getId()->toBinary());

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param Audiobook $audiobook
     * @return AudiobookCategory[]
     */
    public function getAudiobookActiveCategories(Audiobook $audiobook): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->leftJoin('ac.audiobooks', 'a')
            ->where('a.id = :audiobook')
            ->andWhere('ac.active = true')
            ->setParameter('audiobook', $audiobook->getId()->toBinary());

        $query = $qb->getQuery();

        return $query->execute();
    }


    /**
     * @return AudiobookCategory[]
     */
    public function getCategoriesByCountAudiobooks(): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->leftJoin('ac.audiobooks', 'a')
            ->select('COUNT(a) AS HIDDEN audiobooks', 'ac')
            ->where('ac.active = true')
            ->orderBy('audiobooks', "DESC")
            ->groupBy('ac');

        $query = $qb->getQuery();

        return $query->execute();
    }
//    /**
//     * @return AudiobookCategory[] Returns an array of AudiobookCategory objects
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

//    public function findOneBySomeField($value): ?AudiobookCategory
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
