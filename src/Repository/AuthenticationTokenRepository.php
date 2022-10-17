<?php

namespace App\Repository;

use App\Entity\AuthenticationToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthenticationToken>
 *
 * @method AuthenticationToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthenticationToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthenticationToken[]    findAll()
 * @method AuthenticationToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthenticationTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthenticationToken::class);
    }

    /**
     * @param AuthenticationToken $entity
     * @param bool $flush
     * @return void
     */
    public function add(AuthenticationToken $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AuthenticationToken $entity
     * @param bool $flush
     * @return void
     */
    public function remove(AuthenticationToken $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    /**
     * @throws NonUniqueResultException
     */
    public function findActiveToken(string $authorizationHeaderField): ?AuthenticationToken
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.token = :token")
            ->andWhere("a.dateExpired > :dateNow")
            ->setParameter("token", $authorizationHeaderField)
            ->setParameter("dateNow", new \DateTime("now"))
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
    //     * @param User $user
    //     * @return AuthenticationToken[]
    //     */
    public function getActiveUserTenzieResults(User $user):array{

        $qb = $this->createQueryBuilder('tr')
            ->leftJoin("tr.user","u")
            ->andWhere("u.id = :user")
            ->setParameter("user", $user->getId()->toBinary());

        $query = $qb->getQuery();

        return $query->execute();
    }
//    /**
//     * @return AuthenticationToken[] Returns an array of AuthenticationToken objects
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

//    public function findOneBySomeField($value): ?AuthenticationToken
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
