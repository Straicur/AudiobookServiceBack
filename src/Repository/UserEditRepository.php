<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEdit;
use App\Enums\UserEditType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserEdit>
 *
 * @method UserEdit|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserEdit|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserEdit[]    findAll()
 * @method UserEdit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserEditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEdit::class);
    }

    /**
     * @param UserEdit $entity
     * @param bool $flush
     * @return void
     */
    public function add(UserEdit $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param UserEdit $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserEdit $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param User $user
     * @param int $type
     * @return UserEdit|null
     */
    public function checkIfUserCanChange(User $user, int $type): ?UserEdit
    {
        $qb = $this->createQueryBuilder('ue');

        $date = new \DateTime("Now");

        $qb->leftJoin('ue.user', 'u')
            ->where('u.id = :user')
            ->andWhere('((ue.edited = false) AND (ue.editableDate IS NOT NULL AND ue.editableDate > :date))')
            ->andWhere('ue.type = :type')
            ->setParameter('type', $type)
            ->setParameter('date', $date)
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        $res = $query->execute();

        return count($res) > 0 ? $res[0] : null;
    }

    /**
     * @param User $user
     * @return void
     */
    public function changeResetPasswordEdits(User $user): void
    {
        $qb = $this->createQueryBuilder('ue');

        $date = new \DateTime("Now");

        $qb->update()
            ->set("ue.edited", ":edited")
            ->set("ue.editableDate", ":editableDate")
            ->where('ue.user = :user')
            ->andWhere('(ue.edited = false)')
            ->andWhere('ue.type = :type')
            ->setParameter('type', UserEditType::PASSWORD->value)
            ->setParameter('editableDate', $date)
            ->setParameter('edited', true)
            ->setParameter('user', $user->getId()->toBinary());

        $qb->getQuery()->execute();
    }
//    /**
//     * @return UserEdit[] Returns an array of UserEdit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserEdit
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
