<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductView;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductView>
 */
class ProductViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductView::class);
    }

    /**
     * Get view count for a product (last 30 days)
     */
    public function getViewCount(Product $product, \DateTimeImmutable $from = null): int
    {
        if (!$from) {
            $from = new \DateTimeImmutable('-30 days');
        }

        return (int)$this->createQueryBuilder('pv')
            ->select('COUNT(pv.id)')
            ->where('pv.product = :product')
            ->andWhere('pv.viewedAt >= :from')
            ->setParameter('product', $product)
            ->setParameter('from', $from)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get unique viewers count
     */
    public function getUniqueViewerCount(Product $product, \DateTimeImmutable $from = null): int
    {
        if (!$from) {
            $from = new \DateTimeImmutable('-30 days');
        }

        return (int)$this->createQueryBuilder('pv')
            ->select('COUNT(DISTINCT pv.ipAddress)')
            ->where('pv.product = :product')
            ->andWhere('pv.viewedAt >= :from')
            ->setParameter('product', $product)
            ->setParameter('from', $from)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get most viewed products
     */
    public function findMostViewed(int $limit = 10, \DateTimeImmutable $from = null)
    {
        if (!$from) {
            $from = new \DateTimeImmutable('-30 days');
        }

        return $this->createQueryBuilder('pv')
            ->select('pv.product, COUNT(pv.id) as view_count')
            ->where('pv.viewedAt >= :from')
            ->groupBy('pv.product')
            ->setParameter('from', $from)
            ->orderBy('view_count', 'DESC')
            ->setMaxResults($limit)
            ->leftJoin('pv.product', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get user's viewed products
     */
    public function findUserViewed(User $user, int $limit = 20)
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.user = :user')
            ->setParameter('user', $user)
            ->leftJoin('pv.product', 'p')
            ->addSelect('p')
            ->orderBy('pv.viewedAt', 'DESC')
            ->setMaxResults($limit)
            ->groupBy('pv.product')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get product views by day (for analytics)
     */
    public function getViewsByDay(Product $product, int $days = 30)
    {
        $from = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('pv')
            ->select('DATE(pv.viewedAt) as date, COUNT(pv.id) as count')
            ->where('pv.product = :product')
            ->andWhere('pv.viewedAt >= :from')
            ->groupBy('date')
            ->setParameter('product', $product)
            ->setParameter('from', $from)
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Clean old views (older than 90 days)
     */
    public function cleanOldViews(int $days = 90): int
    {
        $from = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('pv')
            ->delete()
            ->where('pv.viewedAt < :date')
            ->setParameter('date', $from)
            ->getQuery()
            ->execute();
    }
}
