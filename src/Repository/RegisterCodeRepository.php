<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RegisterCode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function setCodesToNotActive(User $user): void
    {
        $qb = $this->createQueryBuilder('rc')
            ->update()
            ->set('rc.active', 'false')
            ->where('rc.user = :user')
            ->setParameter('user', $user->getId()->toBinary());

        $qb->getQuery()->execute();
    }
}
