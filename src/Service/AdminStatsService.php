<?php

namespace App\Service;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductViewRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminStatsService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private ProductViewRepository $viewRepository,
        private ReviewRepository $reviewRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {}

    /**
     * Get dashboard overview
     */
    public function getDashboardOverview(): array
    {
        $last30Days = new \DateTimeImmutable('-30 days');

        return [
            'sales' => [
                'total_orders' => $this->getTotalOrders($last30Days),
                'total_revenue' => $this->getTotalRevenue($last30Days),
                'average_order' => $this->getAverageOrderValue($last30Days),
                'trend' => $this->getSalesTrend($last30Days),
            ],
            'products' => [
                'total_products' => $this->getTotalProducts(),
                'new_products' => $this->getNewProducts($last30Days),
                'top_selling' => $this->getTopSellingProducts(5),
                'low_stock' => $this->getLowStockProducts(),
            ],
            'users' => [
                'total_users' => $this->getTotalUsers(),
                'new_users' => $this->getNewUsers($last30Days),
                'active_users' => $this->getActiveUsers($last30Days),
            ],
            'engagement' => [
                'total_reviews' => $this->getTotalReviews(),
                'total_views' => $this->getTotalViews($last30Days),
                'avg_rating' => $this->getAverageRating(),
                'top_products' => $this->getTopViewedProducts(5),
            ],
        ];
    }

    /**
     * Get detailed sales report
     */
    public function getSalesReport(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'summary' => [
                'total_orders' => $this->getTotalOrders($from),
                'total_revenue' => $this->getTotalRevenue($from),
                'average_order' => $this->getAverageOrderValue($from),
                'completed_orders' => $this->getCompletedOrders($from),
                'cancelled_orders' => $this->getCancelledOrders($from),
            ],
            'daily_sales' => $this->getDailySales($from, $to),
            'top_products' => $this->getTopSellingProducts(10),
            'payment_methods' => $this->getPaymentMethodStats($from),
        ];
    }

    /**
     * Get product analytics
     */
    public function getProductAnalytics(): array
    {
        return [
            'total_products' => $this->getTotalProducts(),
            'categories' => $this->getCategoryBreakdown(),
            'top_sellers' => $this->getTopSellingProducts(10),
            'least_sellers' => $this->getLeastSellingProducts(10),
            'reviews_summary' => $this->getReviewsSummary(),
            'inventory_status' => $this->getInventoryStatus(),
        ];
    }

    /**
     * Get user analytics
     */
    public function getUserAnalytics(): array
    {
        $last30 = new \DateTimeImmutable('-30 days');

        return [
            'total_users' => $this->getTotalUsers(),
            'new_users_this_month' => $this->getNewUsers($last30),
            'active_users' => $this->getActiveUsers($last30),
            'inactive_users' => $this->getInactiveUsers($last30),
            'user_growth' => $this->getUserGrowthTrend(),
            'customer_retention' => $this->getCustomerRetention(),
            'top_customers' => $this->getTopCustomers(10),
        ];
    }

    // Helper Methods

    private function getTotalOrders(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(o.id) FROM App\Entity\Order o WHERE o.createdAt >= :from'
        )->setParameter('from', $from)->getSingleScalarResult();
    }

    private function getTotalRevenue(\DateTimeImmutable $from): float
    {
        $result = $this->em->createQuery(
            'SELECT SUM(o.totalPrice) FROM App\Entity\Order o WHERE o.createdAt >= :from'
        )->setParameter('from', $from)->getOneOrNullResult();

        return $result[1] ?? 0;
    }

    private function getAverageOrderValue(\DateTimeImmutable $from): float
    {
        $result = $this->em->createQuery(
            'SELECT AVG(o.totalPrice) FROM App\Entity\Order o WHERE o.createdAt >= :from'
        )->setParameter('from', $from)->getOneOrNullResult();

        return $result[1] ?? 0;
    }

    private function getSalesTrend(\DateTimeImmutable $from): array
    {
        return $this->em->createQuery(
            'SELECT DATE(o.createdAt) as date, COUNT(o.id) as count, SUM(o.totalPrice) as revenue
             FROM App\Entity\Order o WHERE o.createdAt >= :from GROUP BY DATE(o.createdAt) ORDER BY date'
        )->setParameter('from', $from)->getResult();
    }

    private function getTotalProducts(): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(p.id) FROM App\Entity\Product p'
        )->getSingleScalarResult();
    }

    private function getNewProducts(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(p.id) FROM App\Entity\Product p WHERE p.createdAt >= :from'
        )->setParameter('from', $from)->getSingleScalarResult();
    }

    private function getTopSellingProducts(int $limit): array
    {
        return $this->em->createQuery(
            'SELECT p, COUNT(od.id) as order_count, SUM(od.quantity) as total_quantity
             FROM App\Entity\Product p
             LEFT JOIN App\Entity\OrderDetails od WITH od.product = p
             GROUP BY p.id
             ORDER BY total_quantity DESC'
        )->setMaxResults($limit)->getResult();
    }

    private function getLowStockProducts(int $threshold = 10): array
    {
        return $this->em->createQuery(
            'SELECT p FROM App\Entity\Product p WHERE p.quantity < :threshold'
        )->setParameter('threshold', $threshold)->getResult();
    }

    private function getTotalUsers(): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(u.id) FROM App\Entity\User u'
        )->getSingleScalarResult();
    }

    private function getNewUsers(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.createdAt >= :from'
        )->setParameter('from', $from)->getSingleScalarResult();
    }

    private function getActiveUsers(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(DISTINCT v.user) FROM App\Entity\ProductView v WHERE v.viewedAt >= :from'
        )->setParameter('from', $from)->getSingleScalarResult();
    }

    private function getInactiveUsers(\DateTimeImmutable $from): int
    {
        return $this->getTotalUsers() - $this->getActiveUsers($from);
    }

    private function getTotalReviews(): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(r.id) FROM App\Entity\Review r WHERE r.status = :status'
        )->setParameter('status', 'approved')->getSingleScalarResult();
    }

    private function getTotalViews(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(v.id) FROM App\Entity\ProductView v WHERE v.viewedAt >= :from'
        )->setParameter('from', $from)->getSingleScalarResult();
    }

    private function getAverageRating(): float
    {
        $result = $this->em->createQuery(
            'SELECT AVG(r.rating) FROM App\Entity\Review r WHERE r.status = :status'
        )->setParameter('status', 'approved')->getOneOrNullResult();

        return $result[1] ?? 0;
    }

    private function getTopViewedProducts(int $limit): array
    {
        return $this->viewRepository->findMostViewed($limit);
    }

    private function getCompletedOrders(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(o.id) FROM App\Entity\Order o WHERE o.createdAt >= :from AND o.status = :status'
        )->setParameter('from', $from)->setParameter('status', 'completed')->getSingleScalarResult();
    }

    private function getCancelledOrders(\DateTimeImmutable $from): int
    {
        return (int)$this->em->createQuery(
            'SELECT COUNT(o.id) FROM App\Entity\Order o WHERE o.createdAt >= :from AND o.status = :status'
        )->setParameter('from', $from)->setParameter('status', 'cancelled')->getSingleScalarResult();
    }

    private function getDailySales(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->em->createQuery(
            'SELECT DATE(o.createdAt) as date, SUM(o.totalPrice) as revenue, COUNT(o.id) as orders
             FROM App\Entity\Order o WHERE o.createdAt BETWEEN :from AND :to
             GROUP BY DATE(o.createdAt) ORDER BY date'
        )->setParameter('from', $from)->setParameter('to', $to)->getResult();
    }

    private function getPaymentMethodStats(\DateTimeImmutable $from): array
    {
        return $this->em->createQuery(
            'SELECT o.paymentMethod as method, COUNT(o.id) as count, SUM(o.totalPrice) as total
             FROM App\Entity\Order o WHERE o.createdAt >= :from
             GROUP BY o.paymentMethod'
        )->setParameter('from', $from)->getResult();
    }

    private function getCategoryBreakdown(): array
    {
        return $this->em->createQuery(
            'SELECT c, COUNT(p.id) as product_count
             FROM App\Entity\Category c
             LEFT JOIN App\Entity\Product p WITH p.category = c
             GROUP BY c.id'
        )->getResult();
    }

    private function getReviewsSummary(): array
    {
        return $this->em->createQuery(
            'SELECT r.rating, COUNT(r.id) as count FROM App\Entity\Review r
             WHERE r.status = :status GROUP BY r.rating'
        )->setParameter('status', 'approved')->getResult();
    }

    private function getInventoryStatus(): array
    {
        return [
            'in_stock' => $this->getTotalProducts(),
            'low_stock' => count($this->getLowStockProducts()),
            'out_of_stock' => 0,
        ];
    }

    private function getUserGrowthTrend(): array
    {
        return $this->em->createQuery(
            'SELECT DATE(u.createdAt) as date, COUNT(u.id) as count
             FROM App\Entity\User u WHERE u.createdAt >= :from
             GROUP BY DATE(u.createdAt) ORDER BY date'
        )->setParameter('from', new \DateTimeImmutable('-30 days'))->getResult();
    }

    private function getCustomerRetention(): float
    {
        $last30 = new \DateTimeImmutable('-30 days');
        $last60 = new \DateTimeImmutable('-60 days');

        $returning = (int)$this->em->createQuery(
            'SELECT COUNT(DISTINCT o1.user) FROM App\Entity\Order o1, App\Entity\Order o2
             WHERE o1.user = o2.user AND o1.createdAt >= :last30 AND o2.createdAt BETWEEN :last60 AND :last30'
        )->setParameter('last30', $last30)->setParameter('last60', $last60)->getSingleScalarResult();

        $active = $this->getActiveUsers($last60);

        return $active > 0 ? ($returning / $active) * 100 : 0;
    }

    private function getTopCustomers(int $limit): array
    {
        return $this->em->createQuery(
            'SELECT u, COUNT(o.id) as order_count, SUM(o.totalPrice) as total_spent
             FROM App\Entity\User u
             LEFT JOIN App\Entity\Order o WITH o.user = u
             GROUP BY u.id
             ORDER BY total_spent DESC'
        )->setMaxResults($limit)->getResult();
    }
}
