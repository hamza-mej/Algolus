<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Get approved reviews for a product
     */
    public function findApprovedByProduct(Product $product, int $page = 1, int $limit = 10)
    {
        return $this->createQueryBuilder('r')
            ->where('r.product = :product')
            ->andWhere('r.status = :status')
            ->andWhere('r.isVisible = true')
            ->setParameter('product', $product)
            ->setParameter('status', 'approved')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count approved reviews for a product
     */
    public function countApprovedByProduct(Product $product): int
    {
        return (int)$this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.product = :product')
            ->andWhere('r.status = :status')
            ->setParameter('product', $product)
            ->setParameter('status', 'approved')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get average rating for a product
     */
    public function getAverageRating(Product $product): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avg_rating')
            ->where('r.product = :product')
            ->andWhere('r.status = :status')
            ->setParameter('product', $product)
            ->setParameter('status', 'approved')
            ->getQuery()
            ->getOneOrNullResult();

        return $result['avg_rating'] ?? 0;
    }

    /**
     * Get rating distribution for a product
     */
    public function getRatingDistribution(Product $product): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.rating, COUNT(r.id) as count')
            ->where('r.product = :product')
            ->andWhere('r.status = :status')
            ->groupBy('r.rating')
            ->setParameter('product', $product)
            ->setParameter('status', 'approved')
            ->orderBy('r.rating', 'DESC')
            ->getQuery()
            ->getResult();

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($result as $row) {
            $distribution[$row['rating']] = $row['count'];
        }
        return $distribution;
    }

    /**
     * Check if user has reviewed product
     */
    public function hasUserReviewedProduct(User $user, Product $product): bool
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Get user's review of product
     */
    public function findUserReview(User $user, Product $product): ?Review
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get pending reviews (for admin)
     */
    public function findPending(int $limit = 20)
    {
        return $this->createQueryBuilder('r')
            ->where('r.status = :status')
            ->setParameter('status', 'pending')
            ->leftJoin('r.product', 'p')
            ->addSelect('p')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->orderBy('r.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get top rated products
     */
    public function findTopRated(int $limit = 10)
    {
        return $this->createQueryBuilder('r')
            ->select('r.product, AVG(r.rating) as avg_rating, COUNT(r.id) as count')
            ->where('r.status = :status')
            ->groupBy('r.product')
            ->having('COUNT(r.id) > 2')
            ->setParameter('status', 'approved')
            ->orderBy('avg_rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
