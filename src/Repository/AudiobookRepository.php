<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Audiobook;
use App\Entity\AudiobookCategory;
use App\Enums\AudiobookAgeRange;
use App\Enums\AudiobookOrderSearch;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
     * @param array|null $categories
     * @param string|null $author
     * @param string|null $title
     * @param string|null $album
     * @param int|null $duration
     * @param int|null $age
     * @param DateTime|null $year
     * @param int|null $parts
     * @param int|null $order
     * @return Audiobook[]
     */
    public function getAudiobooksByPage(?array $categories = null, ?string $author = null, ?string $title = null, ?string $album = null, ?int $duration = null, ?int $age = null, ?DateTime $year = null, ?int $parts = null, ?int $order = null): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($categories !== null) {
            $qb->innerJoin('a.categories', 'c', Join::WITH, 'c.id IN (:categories)')
                ->setParameter('categories', $categories);
        }
        if ($author !== null) {
            $qb->andWhere('a.author LIKE :author')
                ->setParameter('author', $author);
        }
        if ($title !== null) {
            $qb->andWhere('a.title LIKE :title')
                ->setParameter('title', $title);
        }
        if ($album !== null) {
            $qb->andWhere('a.album LIKE :album')
                ->setParameter('album', $album);
        }

        if ($duration !== null) {

            $durationLow = $duration - 600;
            $durationHigh = $duration + 600;

            $qb->andWhere('((a.duration >= :durationLow) AND (a.duration <= :durationHigh))')
                ->setParameter('durationLow', $durationLow)
                ->setParameter('durationHigh', $durationHigh);
        }

        if ($age !== null) {
            $qb->andWhere('a.age = :age')
                ->setParameter('age', $age);
        }

        if ($year !== null) {
            $yearLow = clone $year;
            $yearLow->modify('-1 year');

            $yearHigh = clone $year;
            $yearHigh->modify('+1 year');

            $qb->andWhere('((a.year > :yearLow) AND (a.year < :yearHigh))')
                ->setParameter('yearLow', $yearLow)
                ->setParameter('yearHigh', $yearHigh);
        }

        if ($parts !== null) {
            $partsLow = $parts - 1;
            $partsHigh = $parts + 1;

            $qb->andWhere('((a.parts >= :partsLow) AND (a.parts <= :partsHigh))')
                ->setParameter('partsLow', $partsLow)
                ->setParameter('partsHigh', $partsHigh);
        }

        if ($order !== null) {
            switch ($order) {
                case AudiobookOrderSearch::POPULAR->value:
                {
                    $qb->innerJoin('a.audiobookInfos', 'ai')
                        ->groupBy('a')
                        ->orderBy('COUNT(ai)', 'DESC');
                    break;
                }
                case AudiobookOrderSearch::LEST_POPULAR->value:
                {
                    $qb->innerJoin('a.audiobookInfos', 'ai')
                        ->groupBy('a')
                        ->orderBy('COUNT(ai)', 'ASC');
                    break;
                }
                case AudiobookOrderSearch::LATEST->value:
                {
                    $qb->orderBy('a.dateAdd', 'DESC');
                    break;
                }
                case AudiobookOrderSearch::OLDEST->value:
                {
                    $qb->orderBy('a.dateAdd', 'ASC');
                    break;
                }
                case AudiobookOrderSearch::ALPHABETICAL_DESC->value:
                {
                    $qb->orderBy('a.title', 'DESC');
                    break;
                }
                case AudiobookOrderSearch::ALPHABETICAL_ASC->value:
                {
                    $qb->orderBy('a.title', 'ASC');
                    break;
                }
                case AudiobookOrderSearch::TOP_RATED->value:
                {
                    $qb->orderBy('a.avgRating', 'DESC');
                    break;
                }
                case AudiobookOrderSearch::WORST_RATED->value:
                {
                    $qb->orderBy('a.avgRating', 'ASC');
                    break;
                }
            }
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param AudiobookCategory $category
     * @param AudiobookAgeRange|null $age
     * @return Audiobook[]
     */
    public function getActiveCategoryAudiobooks(AudiobookCategory $category, ?AudiobookAgeRange $age = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.categories', 'c', Join::WITH, 'c.id = :category')
            ->where('a.active = true')
            ->setParameter('category', $category->getId()->toBinary());

        if ($age !== null) {
            $qb->andWhere('a.age <= :age')
                ->setParameter('age', $age->value);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * @param AudiobookCategory $category
     * @return Audiobook[]
     */
    public function getCategoryAudiobooks(AudiobookCategory $category): array
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.categories', 'c', Join::WITH, 'c.id = :category')
            ->setParameter('category', $category->getId()->toBinary());

        return $qb->getQuery()->execute();
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
            ->innerJoin('a.categories', 'c', Join::WITH, 'c.categoryKey = :categoryKey')
            ->where('a.id = :audiobookId');
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
            ->innerJoin('a.audiobookRatings', 'ar')
            ->where('a.active = true')
            ->groupBy('a')
            ->orderBy('COUNT(ar)', 'DESC')
            ->setMaxResults(3);

        return $qb->getQuery()->execute();
    }

    /**
     * @param string $title
     * @param AudiobookAgeRange|null $age
     * @return Audiobook[]
     */
    public function searchAudiobooksByName(string $title, ?AudiobookAgeRange $age = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.audiobookRatings', 'ar')
            ->where('a.active = true')
            ->andWhere('((a.title LIKE :title) OR (a.author LIKE :title))')
            ->setParameter('title', '%' . $title . '%');

        if ($age !== null) {
            $qb->andWhere('a.age <= :age')
                ->setParameter('age', $age->value);
        }

        $qb->groupBy('a')
            ->orderBy('COUNT(ar)', 'DESC');

        return $qb->getQuery()->execute();
    }
}
