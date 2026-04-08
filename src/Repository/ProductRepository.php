<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
        $this->paginator = $paginator;
    }

//    public function findColor(int $search): QueryBuilder
//    {
////        $entityManager = $this->getEntityManager();
//
////        $query = $this->createQueryBuilder(
////            'SELECT *
////            FROM App\Entity\Product p, App\Entity\Details d,
////            WHERE p == :id
////            AND d.productId == :id
////            ORDER BY p.id ASC'
////        )->setParameter('id', $search);
//        $query = $this
//            ->createQueryBuilder('p')
//            ->select('d','p')
//            ->join('p.category','d')
//            ->select('color','p')
//            ->join('p.color','color')
//            ->select('size','p')
//            ->join('p.size','size');
//
//
//        // returns an array of Product objects
//        return $query;
//    }

    /**
     * @param SearchData $search
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search,$maxItemPerPage=6): PaginationInterface
    {

        $query = $this->getSearchQuery($search)->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            max(1, (int) $maxItemPerPage)
        );
    }

    /**
     * @return integer[]
     */
    public function findMinMax(SearchData $search): array
    {
        $results = $this->getSearchQuery($search, true)
            ->select('MIN(p.productPrice) as min', 'MAX(p.productPrice) as max')
            ->getQuery()
            ->setResultCacheId('product_min_max_prices')
            ->setResultCacheLifetime(3600)
            ->enableResultCache()
            ->getScalarResult();
        return [(int)$results[0]['min'], (int)$results[0]['max']];
    }

    private function getSearchQuery(SearchData $search, $ignorePrice = false): QueryBuilder
    {
        // Optimized eager loading - only select once with multiple joins
        $query = $this
            ->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('p.color', 'color')
            ->addSelect('color')
            ->leftJoin('p.size', 'size')
            ->addSelect('size');

        // Search filter
        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.productName LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        // Price filters
        if ($search->min !== null && $search->min !== '' && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.productPrice >= :min')
                ->setParameter('min', $search->min);
        }

        if ($search->max !== null && $search->max !== '' && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.productPrice <= :max')
                ->setParameter('max', $search->max);
        }

        // Sale filter
        if (!empty($search->onSale)) {
            $query = $query
                ->andWhere('p.onSale = 1');
        }

        // Category filter
        if (!empty($search->categories)) {
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }

        // Color filter
        if (!empty($search->color)) {
            $query = $query
                ->andWhere('color.id IN (:color)')
                ->setParameter('color', $search->color);
        }

        // Size filter
        if (!empty($search->size)) {
            $query = $query
                ->andWhere('size.id IN (:size)')
                ->setParameter('size', $search->size);
        }

        // Order by product ID descending for consistency and performance
        if (!$ignorePrice) {
            $query = $query->orderBy('p.id', 'DESC');
        }

        return $query;
    }

}
