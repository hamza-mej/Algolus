<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class AdvancedAnalyticsService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Get comprehensive sales analytics
     */
    public function getSalesAnalytics(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $dql = 'SELECT 
                    o.id,
                    o.totalAmount as amount,
                    o.status,
                    o.paymentMethod,
                    DATE(o.createdAt) as date,
                    u.id as userId
                FROM App\Entity\Order o
                JOIN o.user u
                WHERE o.createdAt >= :from AND o.createdAt <= :to
                ORDER BY o.createdAt DESC';

        $orders = $this->em->createQuery($dql)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getResult();

        $byDate = [];
        $byStatus = [];
        $byPayment = [];
        $totalRevenue = 0;

        foreach ($orders as $order) {
            $date = $order['date']->format('Y-m-d');
            $status = $order['status'];
            $payment = $order['paymentMethod'];
            $amount = (float)$order['amount'];

            // By date
            if (!isset($byDate[$date])) {
                $byDate[$date] = ['count' => 0, 'revenue' => 0];
            }
            $byDate[$date]['count']++;
            $byDate[$date]['revenue'] += $amount;

            // By status
            if (!isset($byStatus[$status])) {
                $byStatus[$status] = ['count' => 0, 'revenue' => 0];
            }
            $byStatus[$status]['count']++;
            $byStatus[$status]['revenue'] += $amount;

            // By payment method
            if (!isset($byPayment[$payment])) {
                $byPayment[$payment] = ['count' => 0, 'revenue' => 0];
            }
            $byPayment[$payment]['count']++;
            $byPayment[$payment]['revenue'] += $amount;

            $totalRevenue += $amount;
        }

        return [
            'totalOrders' => count($orders),
            'totalRevenue' => $totalRevenue,
            'averageOrderValue' => count($orders) > 0 ? $totalRevenue / count($orders) : 0,
            'byDate' => $byDate,
            'byStatus' => $byStatus,
            'byPaymentMethod' => $byPayment,
        ];
    }

    /**
     * Get customer lifetime value report
     */
    public function getCustomerLTV(): array
    {
        $dql = 'SELECT 
                    u.id,
                    u.email,
                    COUNT(o.id) as orderCount,
                    SUM(o.totalAmount) as totalSpent,
                    MAX(o.createdAt) as lastOrderDate
                FROM App\Entity\User u
                LEFT JOIN u.orders o
                GROUP BY u.id
                ORDER BY totalSpent DESC';

        $customers = $this->em->createQuery($dql)->getResult();

        $ltv = [];
        $totalLTV = 0;

        foreach ($customers as $customer) {
            $spent = (float)($customer['totalSpent'] ?? 0);
            $orders = (int)$customer['orderCount'];

            $ltv[] = [
                'userId' => $customer['id'],
                'email' => $customer['email'],
                'orders' => $orders,
                'ltv' => $spent,
                'averageOrderValue' => $orders > 0 ? $spent / $orders : 0,
                'lastOrderDate' => $customer['lastOrderDate'],
            ];

            $totalLTV += $spent;
        }

        return [
            'topCustomers' => array_slice($ltv, 0, 10),
            'totalCustomerValue' => $totalLTV,
            'averageCustomerValue' => count($ltv) > 0 ? $totalLTV / count($ltv) : 0,
            'totalCustomers' => count($ltv),
        ];
    }

    /**
     * Get product performance report
     */
    public function getProductPerformance(): array
    {
        $dql = 'SELECT 
                    p.id,
                    p.productName,
                    p.productPrice,
                    COUNT(oi.id) as sales,
                    SUM(oi.quantity) as unitsSold,
                    AVG(r.rating) as avgRating,
                    COUNT(DISTINCT r.id) as reviewCount,
                    COUNT(w.id) as wishlistCount,
                    COUNT(pv.id) as viewCount
                FROM App\Entity\Product p
                LEFT JOIN App\Entity\OrderItem oi WITH oi.product = p
                LEFT JOIN App\Entity\Review r WITH r.product = p
                LEFT JOIN App\Entity\Wishlist w WITH w.product = p
                LEFT JOIN App\Entity\ProductView pv WITH pv.product = p
                GROUP BY p.id
                ORDER BY sales DESC';

        $products = $this->em->createQuery($dql)->getResult();

        $performance = [];
        foreach ($products as $product) {
            $unitsSold = (int)($product['unitsSold'] ?? 0);
            $price = (float)$product['productPrice'];
            $sales = (int)($product['sales'] ?? 0);

            $performance[] = [
                'productId' => $product['id'],
                'name' => $product['productName'],
                'price' => $price,
                'sales' => $sales,
                'unitsSold' => $unitsSold,
                'revenue' => $unitsSold * $price,
                'avgRating' => $product['avgRating'] ? round($product['avgRating'], 1) : 0,
                'reviews' => (int)$product['reviewCount'],
                'wishlistCount' => (int)$product['wishlistCount'],
                'viewCount' => (int)$product['viewCount'],
            ];
        }

        return [
            'topProducts' => array_slice($performance, 0, 20),
            'totalProductsAnalyzed' => count($performance),
        ];
    }

    /**
     * Get conversion funnel report
     */
    public function getConversionFunnel(): array
    {
        // Unique visitors
        $visitors = $this->em->createQuery(
            'SELECT COUNT(DISTINCT pv.user) FROM App\Entity\ProductView pv'
        )->getSingleScalarResult();

        // Users with cart items
        $cartUsers = $this->em->createQuery(
            'SELECT COUNT(DISTINCT c.user) FROM App\Entity\CartItem c'
        )->getSingleScalarResult();

        // Completed orders
        $orders = $this->em->createQuery(
            'SELECT COUNT(DISTINCT o.user) FROM App\Entity\Order o WHERE o.status = :status'
        )->setParameter('status', 'completed')->getSingleScalarResult();

        return [
            'visitors' => (int)$visitors,
            'addedToCart' => (int)$cartUsers,
            'purchased' => (int)$orders,
            'cartConversionRate' => $visitors > 0 ? round(($cartUsers / $visitors) * 100, 2) : 0,
            'purchaseConversionRate' => $cartUsers > 0 ? round(($orders / $cartUsers) * 100, 2) : 0,
            'overallConversionRate' => $visitors > 0 ? round(($orders / $visitors) * 100, 2) : 0,
        ];
    }

    /**
     * Get engagement metrics
     */
    public function getEngagementMetrics(): array
    {
        // Reviews submitted
        $reviews = $this->em->createQuery(
            'SELECT COUNT(r) as count FROM App\Entity\Review r'
        )->getSingleResult()['count'];

        // Wishlist items
        $wishlistItems = $this->em->createQuery(
            'SELECT COUNT(w) as count FROM App\Entity\Wishlist w'
        )->getSingleResult()['count'];

        // Active users (viewed products in last 30 days)
        $activeUsers = $this->em->createQuery(
            'SELECT COUNT(DISTINCT pv.user) FROM App\Entity\ProductView pv
             WHERE pv.createdAt >= :since'
        )->setParameter('since', new \DateTimeImmutable('-30 days'))->getSingleScalarResult();

        // Newsletter subscribers
        $subscribers = $this->em->createQuery(
            'SELECT COUNT(ns) FROM App\Entity\NewsletterSubscriber ns WHERE ns.status = :status'
        )->setParameter('status', 'subscribed')->getSingleScalarResult();

        return [
            'totalReviews' => (int)$reviews,
            'totalWishlistItems' => (int)$wishlistItems,
            'activeUsers30Days' => (int)$activeUsers,
            'newsletterSubscribers' => (int)$subscribers,
        ];
    }

    /**
     * Export analytics to CSV
     */
    public function exportAnalyticsToCSV(\DateTimeImmutable $from, \DateTimeImmutable $to): string
    {
        $analytics = $this->getSalesAnalytics($from, $to);

        $csv = "Date,Orders,Revenue\n";
        foreach ($analytics['byDate'] as $date => $data) {
            $csv .= "{$date},{$data['count']},{$data['revenue']}\n";
        }

        return $csv;
    }

    /**
     * Generate report PDF (requires dompdf library)
     */
    public function generatePDFReport(\DateTimeImmutable $from, \DateTimeImmutable $to): string
    {
        $data = [
            'sales' => $this->getSalesAnalytics($from, $to),
            'ltv' => $this->getCustomerLTV(),
            'products' => $this->getProductPerformance(),
            'funnel' => $this->getConversionFunnel(),
            'engagement' => $this->getEngagementMetrics(),
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
