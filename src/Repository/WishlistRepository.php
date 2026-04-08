<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    /**
     * Get user's wishlist
     */
    public function findByUser(User $user, int $page = 1, int $limit = 20)
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->leftJoin('w.product', 'p')
            ->addSelect('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->orderBy('w.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count user's wishlist items
     */
    public function countByUser(User $user): int
    {
        return (int)$this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Check if product is in user's wishlist
     */
    public function isInWishlist(User $user, Product $product): bool
    {
        $count = $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Find wishlist item
     */
    public function findWishlistItem(User $user, Product $product): ?Wishlist
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get most wished products (popular items)
     */
    public function findMostWished(int $limit = 10)
    {
        return $this->createQueryBuilder('w')
            ->select('w.product, COUNT(w.id) as wish_count')
            ->leftJoin('w.product', 'p')
            ->addSelect('p')
            ->groupBy('w.product')
            ->orderBy('wish_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get products on sale that are in user's wishlist
     */
    public function findWishlistOnSale(User $user)
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->leftJoin('w.product', 'p')
            ->addSelect('p')
            ->andWhere('p.onSale = true')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
