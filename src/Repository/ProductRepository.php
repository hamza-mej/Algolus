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

    /**
     * @param SearchData $search
     * @return PaginationInterface
     */
    public function findSearch(SearchData $search,$maxItemPerPage=2): PaginationInterface
    {

        $query = $this->getSearchQuery($search)->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            5
//            $maxItemPerPage
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
            ->getScalarResult();
        return [(int)$results[0]['min'], (int)$results[0]['max']];
    }

    private function getSearchQuery(SearchData $search, $ignorePrice = false): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('p')
            ->select('c','p')
            ->join('p.category','c')
            ->select('color','p')
            ->join('p.color','color')
            ->select('size','p')
            ->join('p.size','size');

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('p.productName LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if (!empty($search->min) && $ignorePrice === false ) {
            $query = $query
                ->andWhere('p.productPrice >= :min')
                ->setParameter('min', $search->min);
        }

        if (!empty($search->max) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.productPrice <= :max')
                ->setParameter('max', $search->max);
        }

        if (!empty($search->onSale)) {
            $query = $query
                ->andWhere('p.onSale = 1');
        }

        if (!empty($search->categories)) {
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }

        if (!empty($search->color)) {
            $query = $query
                ->andWhere('color.id IN (:color)')
                ->setParameter('color', $search->color);
        }

        if (!empty($search->size)) {
            $query = $query
                ->andWhere('size.id IN (:size)')
                ->setParameter('size', $search->size);
        }

        return $query;
    }

}
