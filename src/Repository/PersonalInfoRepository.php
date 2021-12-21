<?php

namespace App\Repository;

use App\Entity\PersonalInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PersonalInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonalInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonalInfo[]    findAll()
 * @method PersonalInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonalInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonalInfo::class);
    }

    // /**
    //  * @return PersonalInfo[] Returns an array of PersonalInfo objects
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
    public function findOneBySomeField($value): ?PersonalInfo
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
