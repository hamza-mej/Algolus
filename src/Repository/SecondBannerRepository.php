<?php

namespace App\Repository;

use App\Entity\SecondBanner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SecondBanner|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecondBanner|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecondBanner[]    findAll()
 * @method SecondBanner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecondBannerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecondBanner::class);
    }

    // /**
    //  * @return SecondBanner[] Returns an array of SecondBanner objects
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
    public function findOneBySomeField($value): ?SecondBanner
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
