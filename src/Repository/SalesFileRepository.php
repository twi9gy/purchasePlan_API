<?php

namespace App\Repository;

use App\Entity\SalesFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SalesFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method SalesFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method SalesFile[]    findAll()
 * @method SalesFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalesFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SalesFile::class);
    }

    public function findByCategory($user, $category): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.filename, s.separator')
            ->andWhere('s.purchase_user = :user')
            ->andWhere('s.category = :category')
            ->setParameter('user', $user)
            ->setParameter('category', $category)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getArrayResult()
            ;
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.filename, s.separator')
            ->andWhere('s.purchase_user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getArrayResult()
            ;
    }

    // /**
    //  * @return SalesFile[] Returns an array of SalesFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SalesFile
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
