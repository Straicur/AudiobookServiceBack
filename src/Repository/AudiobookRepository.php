<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Audiobook>
 *
 * @method Audiobook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Audiobook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Audiobook[]    findAll()
 * @method Audiobook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudiobookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Audiobook::class);
    }

    /**
     * @param Audiobook $entity
     * @param bool $flush
     * @return void
     */
    public function add(Audiobook $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Audiobook $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Audiobook $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param int $limit
     * @param int $page
     * @return Audiobook[]
     */
    public function getAudiobooksByPage( int $page, int $limit): array
    {
        $minResult = $page * $limit;
        $maxResult = $limit + $minResult;

        $qb = $this->createQueryBuilder('a')
            ->orderBy("a.dateAdd", "DESC")
            ->setFirstResult($minResult)
            ->setMaxResults($maxResult);

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param AudiobookCategory $category
     * @return Audiobook[]
     */
    public function getRandomSortedCategoryAudiobooks(AudiobookCategory $category): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('c.id = :category')
            ->andWhere('a.active = true')
            ->setParameter('category', $category->getId()->toBinary());

        $query = $qb->getQuery();

        return $query->execute();
    }
//    /**
//     * @return Audiobook[] Returns an array of Audiobook objects
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

//    public function findOneBySomeField($value): ?Audiobook
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
