<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\ProductViewRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecommendationEngine
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductViewRepository $viewRepository,
        private EntityManagerInterface $em
    ) {}

    /**
     * Get personalized recommendations for user
     */
    public function getRecommendationsForUser(User $user, int $limit = 6): array
    {
        $recommendations = [];

        // 1. Products from same category as viewed products (40%)
        $categoryBased = $this->getCategoryBasedRecommendations($user, (int)($limit * 0.4));
        $recommendations = array_merge($recommendations, $categoryBased);

        // 2. Popular products in user's view history (30%)
        $popular = $this->getPopularRecommendations($user, (int)($limit * 0.3));
        $recommendations = array_merge($recommendations, $popular);

        // 3. Recently viewed products (similar to what user views) (30%)
        $trending = $this->getTrendingRecommendations((int)($limit * 0.3));
        $recommendations = array_merge($recommendations, $trending);

        // Remove duplicates and limit
        $unique = [];
        $ids = [];
        foreach ($recommendations as $product) {
            if (!in_array($product->getId(), $ids)) {
                $unique[] = $product;
                $ids[] = $product->getId();
                if (count($unique) >= $limit) {
                    break;
                }
            }
        }

        return $unique;
    }

    /**
     * Get recommendations based on user's category interests
     */
    private function getCategoryBasedRecommendations(User $user, int $limit): array
    {
        // Get categories user viewed products from
        $userViews = $this->viewRepository->findUserViewed($user, 50);
        $categoryIds = [];

        foreach ($userViews as $view) {
            $product = $view->getProduct();
            if ($product->getCategory()) {
                $categoryIds[] = $product->getCategory()->getId();
            }
        }

        if (empty($categoryIds)) {
            return [];
        }

        return $this->productRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('c.id IN (:categoryIds)')
            ->setParameter('categoryIds', array_unique($categoryIds))
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get popular products (most viewed)
     */
    private function getPopularRecommendations(User $user, int $limit): array
    {
        $mostViewed = $this->viewRepository->findMostViewed($limit);

        return array_map(fn($item) => $item['product'], $mostViewed);
    }

    /**
     * Get trending products
     */
    private function getTrendingRecommendations(int $limit): array
    {
        $last7Days = new \DateTimeImmutable('-7 days');

        return $this->productRepository->createQueryBuilder('p')
            ->leftJoin('App\Entity\ProductView', 'v', 'WITH', 'v.product = p.id')
            ->where('v.viewedAt >= :date')
            ->groupBy('p.id')
            ->having('COUNT(v.id) > 5')
            ->setParameter('date', $last7Days)
            ->orderBy('COUNT(v.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get products similar to a specific product
     */
    public function getSimilarProducts(Product $product, int $limit = 6): array
    {
        if (!$product->getCategory()) {
            return [];
        }

        return $this->productRepository->createQueryBuilder('p')
            ->where('p.category = :category')
            ->andWhere('p.id != :productId')
            ->setParameter('category', $product->getCategory())
            ->setParameter('productId', $product->getId())
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get products by color/size (for cross-selling)
     */
    public function getRelatedProducts(Product $product, int $limit = 4): array
    {
        $qb = $this->productRepository->createQueryBuilder('p')
            ->where('p.id != :productId')
            ->setParameter('productId', $product->getId());

        if ($product->getColor()) {
            $qb->orWhere('p.color = :color')
                ->setParameter('color', $product->getColor());
        }

        if ($product->getSize()) {
            $qb->orWhere('p.size = :size')
                ->setParameter('size', $product->getSize());
        }

        return $qb->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get "Customers also viewed" for product
     */
    public function getAlsoViewedProducts(Product $product, int $limit = 4): array
    {
        return $this->productRepository->createQueryBuilder('p')
            ->leftJoin('App\Entity\ProductView', 'v1', 'WITH', 'v1.product = :productId')
            ->leftJoin('App\Entity\ProductView', 'v2', 'WITH', 'v2.product = p.id AND v2.user = v1.user')
            ->where('p.id != :productId')
            ->setParameter('productId', $product->getId())
            ->groupBy('p.id')
            ->having('COUNT(v2.id) > 0')
            ->orderBy('COUNT(v2.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get personalized product feed
     */
    public function getPersonalizedFeed(User $user, int $limit = 12): array
    {
        // Mix of recommendations and new products
        $recommendations = $this->getRecommendationsForUser($user, (int)($limit * 0.7));

        $newProducts = $this->productRepository->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults((int)($limit * 0.3))
            ->getQuery()
            ->getResult();

        return array_merge($recommendations, $newProducts);
    }
}
