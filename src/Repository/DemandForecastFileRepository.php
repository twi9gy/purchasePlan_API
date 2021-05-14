<?php

namespace App\Repository;

use App\Entity\DemandForecastFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DemandForecastFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandForecastFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandForecastFile[]    findAll()
 * @method DemandForecastFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandForecastFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandForecastFile::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByFilename($value): ?DemandForecastFile
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.filename = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
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
    //  * @return DemandForecastFile[] Returns an array of DemandForecastFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DemandForecastFile
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
