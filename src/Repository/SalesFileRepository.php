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
