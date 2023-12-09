<?php

namespace App\Repository;

use App\Entity\ProposedAudiobooks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProposedAudiobooks>
 *
 * @method ProposedAudiobooks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProposedAudiobooks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProposedAudiobooks[]    findAll()
 * @method ProposedAudiobooks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProposedAudiobooksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProposedAudiobooks::class);
    }

    /**
     * @param ProposedAudiobooks $entity
     * @param bool $flush
     * @return void
     */
    public function add(ProposedAudiobooks $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param ProposedAudiobooks $entity
     * @param bool $flush
     * @return void
     */
    public function remove(ProposedAudiobooks $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return ProposedAudiobooks[] Returns an array of ProposedAudiobooks objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProposedAudiobooks
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
