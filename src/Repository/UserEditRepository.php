<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEdit;
use App\Enums\UserEditType;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function add(UserEdit $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserEdit $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function checkIfUserCanChange(User $user, UserEditType $type): ?UserEdit
    {
        $qb = $this->createQueryBuilder('ue');

        $date = new DateTime();

        return $qb->innerJoin('ue.user', 'u', Join::WITH, 'u.id = :user')
            ->where('((ue.edited = false) AND (ue.editableDate IS NOT NULL AND ue.editableDate > :date))')
            ->andWhere('ue.type = :type')
            ->setParameter('type', $type->value)
            ->setParameter('date', $date)
            ->setParameter('user', $user->getId()->toBinary())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function checkIfUserCanChangeWithCode(User $user, UserEditType $type, string $code): ?UserEdit
    {
        $qb = $this->createQueryBuilder('ue');

        $date = new DateTime();

        return $qb->innerJoin('ue.user', 'u', Join::WITH, 'u.id = :user')
            ->where('((ue.edited = false) AND (ue.editableDate IS NOT NULL AND ue.editableDate > :date))')
            ->andWhere('ue.type = :type')
            ->andWhere('ue.code = :code')
            ->setParameter('type', $type->value)
            ->setParameter('date', $date)
            ->setParameter('code', $code)
            ->setParameter('user', $user->getId()->toBinary())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function changeResetPasswordEdits(User $user): void
    {
        $qb = $this->createQueryBuilder('ue');

        $date = new DateTime();

        $qb->update()
            ->set('ue.edited', ':edited')
            ->set('ue.editableDate', ':editableDate')
            ->where('ue.user = :user')
            ->andWhere('(ue.edited = false)')
            ->andWhere('ue.type = :type')
            ->setParameter('type', UserEditType::PASSWORD->value)
            ->setParameter('editableDate', $date)
            ->setParameter('edited', true)
            ->setParameter('user', $user->getId()->toBinary());

        $query = $qb->getQuery();

        $query->execute();
    }
}
