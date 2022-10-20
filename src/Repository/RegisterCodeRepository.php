<?php

namespace App\Repository;

use App\Entity\AuthenticationToken;
use App\Entity\RegisterCode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RegisterCode>
 *
 * @method RegisterCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method RegisterCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method RegisterCode[]    findAll()
 * @method RegisterCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegisterCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RegisterCode::class);
    }

    /**
     * @param RegisterCode $entity
     * @param bool $flush
     * @return void
     */
    public function add(RegisterCode $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param RegisterCode $entity
     * @param bool $flush
     * @return void
     */
    public function remove(RegisterCode $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param User $user
     * @return void
     */
    public function setCodesToNotActive(User $user):void{
        $qb = $this->createQueryBuilder('rc')
            ->set("rc.active","false")
            ->leftJoin('rc.user', 'u')
            ->where("u.id = :user")
            ->setParameter("user", $user->getId()->toBinary());

        $query = $qb->getQuery();
    }
//    /**
//     * @return RegisterCode[] Returns an array of RegisterCode objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RegisterCode
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
