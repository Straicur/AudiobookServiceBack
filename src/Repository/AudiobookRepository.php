<?php

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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
    public function getAudiobooksByPage(int $page, int $limit): array
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
    public function getActiveCategoryAudiobooks(AudiobookCategory $category): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('c.id = :category')
            ->andWhere('a.active = true')
            ->setParameter('category', $category->getId()->toBinary());

        $query = $qb->getQuery();

        return $query->execute();
    }

    /**
     * @param Uuid $audiobookId
     * @param string $categoryKey
     * @param bool $getActive
     * @return Audiobook|null
     */
    public function getAudiobookByCategoryKeyAndId(Uuid $audiobookId, string $categoryKey, bool $getActive = true): ?Audiobook
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'c')
            ->where('c.categoryKey = :categoryKey')
            ->andWhere('a.id = :audiobookId');
        if ($getActive) {
            $qb->andWhere('a.active = true');
        }
        $qb->setParameter('audiobookId', $audiobookId->toBinary())
            ->setParameter('categoryKey', $categoryKey);

        $query = $qb->getQuery();

        $res = $query->execute();

        return count($res) > 0 ? $res[0] : null;
    }

    /**
     * @return Audiobook[]
     */
    public function getBestAudiobooks(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.audiobookRatings', 'ar')
            ->where('a.active = true')
            ->groupBy('a')
            ->orderBy('COUNT(ar)', "DESC")
            ->setMaxResults(3);

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
