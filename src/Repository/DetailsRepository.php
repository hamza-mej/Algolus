<?php

namespace App\Repository;

use App\Entity\Details;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Details|null find($id, $lockMode = null, $lockVersion = null)
 * @method Details|null findOneBy(array $criteria, array $orderBy = null)
 * @method Details[]    findAll()
 * @method Details[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Details::class);
    }

    public function findSizeOfColor(int $id, $search): array
    {
        $query = $this
            ->createQueryBuilder('d')
        ;
        $query
            ->select('d.Size')
            ->where($query->expr()->eq('d.Color', ':color'))
            ->setParameter('color', $search)
            ->andWhere($query->expr()->eq('d.product', ':id'))
            ->setParameter('id', $id);

        // returns an array of Product objects
        return $query->getQuery()->getResult();
    }


    public function findColor(int $search): array
    {
        $query = $this
            ->createQueryBuilder('d')
            ;
        $query
            ->select('d.Color')
            ->where($query->expr()->eq('d.product', ':id'))
            ->setParameter('id', $search)
            ->distinct();

        // returns an array of Product objects
        return $query->getQuery()->getResult();
    }

    public function findSize(int $search): array
    {
        $query = $this
            ->createQueryBuilder('d')
        ;
        $query
            ->select('d.Size')
            ->where($query->expr()->eq('d.product', ':id'))
            ->setParameter('id', $search)
            ->distinct();

        // returns an array of Product objects
        return $query->getQuery()->getResult();
    }



//            $query = $em->createQuery(
//            'SELECT productName
//                                    FROM App\Entity\Product p
//                                    WHERE p.productName LIKE :cp'
//            )->setParameter('$item->ProductSelected', '%'.$cp.'%');
//
//            $villeCp = $query->execute();



    // /**
    //  * @return Details[] Returns an array of Details objects
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
    public function findOneBySomeField($value): ?Details
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
