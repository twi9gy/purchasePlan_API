<?php

namespace App\Repository;

use App\Entity\PurchasePlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PurchasePlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchasePlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchasePlan[]    findAll()
 * @method PurchasePlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchasePlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchasePlan::class);
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, d.filename')
            ->andWhere('d.purchase_user = :user')
            ->setParameter('user', $user)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getArrayResult()
            ;
    }

    // /**
    //  * @return PurchasePlan[] Returns an array of PurchasePlan objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PurchasePlan
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
