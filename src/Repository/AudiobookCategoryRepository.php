<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AudiobookCategory $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AudiobookCategory $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeCategoryAndChildren(AudiobookCategory $category): void
    {
        $childCategories = $this->createQueryBuilder('ac')
            ->where('ac.parent = :category')
            ->setParameter('category', $category->getId()->toBinary())
            ->getQuery()
            ->execute();

        foreach ($childCategories as $childCategory) {
            $this->removeCategoryAndChildren($childCategory);
        }

        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Audiobook $audiobook
     * @return AudiobookCategory[]
     */
    public function getAudiobookCategories(Audiobook $audiobook): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->innerJoin('ac.audiobooks', 'a', Join::WITH, 'a.id = :audiobook')
            ->setParameter('audiobook', $audiobook->getId()->toBinary());

        return $qb->getQuery()->execute();
    }

    /**
     * @param Audiobook $audiobook
     * @return AudiobookCategory[]
     */
    public function getAudiobookActiveCategories(Audiobook $audiobook): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->innerJoin('ac.audiobooks', 'a', Join::WITH, 'a.id = :audiobook')
            ->where('ac.active = true')
            ->setParameter('audiobook', $audiobook->getId()->toBinary());

        return $qb->getQuery()->execute();
    }


    /**
     * @return AudiobookCategory[]
     */
    public function getCategoriesByCountAudiobooks(): array
    {
        $qb = $this->createQueryBuilder('ac')
            ->innerJoin('ac.audiobooks', 'a')
            ->select('COUNT(a.id) AS HIDDEN audiobooks', 'ac')
            ->where('ac.active = true')
            ->andWhere('a.active = true')
            ->having('count(a.id) > 0')
            ->orderBy('audiobooks', 'DESC')
            ->groupBy('ac');

        return $qb->getQuery()->execute();
    }
}
