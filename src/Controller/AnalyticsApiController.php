<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductView;
use App\Repository\ProductRepository;
use App\Repository\ProductViewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/analytics', name: 'api_analytics')]
class AnalyticsApiController extends AbstractController
{
    /**
     * Track product view
     */
    #[Route('/track-view', name: '_track_view', methods: ['POST'])]
    public function trackProductView(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        $duration = (int)($data['duration'] ?? 0);

        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], 400);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $view = new ProductView();
        $view->setProduct($product)
            ->setUser($this->getUser())
            ->setDuration($duration)
            ->setIpAddress($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setReferrer($request->headers->get('Referer'));

        $em->persist($view);
        $em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Track search query
     */
    #[Route('/track-search', name: '_track_search', methods: ['POST'])]
    public function trackSearch(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $query = $data['query'] ?? '';
        $resultCount = $data['resultCount'] ?? 0;

        // Log search - could be stored in database or file
        // For now, just acknowledge
        return $this->json([
            'success' => true,
            'message' => 'Search tracked',
        ]);
    }

    /**
     * Track form submission
     */
    #[Route('/track-event', name: '_track_event', methods: ['POST'])]
    public function trackEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $eventType = $data['type'] ?? 'unknown'; // view, click, submit, etc.
        $eventData = $data['data'] ?? [];

        // Log event - could be stored for analytics
        return $this->json([
            'success' => true,
            'message' => 'Event tracked',
        ]);
    }

    /**
     * Get product view statistics (for admin)
     */
    #[Route('/product-stats/{id}', name: '_product_stats', methods: ['GET'])]
    public function getProductStats(
        int $id,
        ProductRepository $productRepository,
        ProductViewRepository $viewRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $last30 = new \DateTimeImmutable('-30 days');
        $viewCount = $viewRepository->getViewCount($product, $last30);
        $uniqueViewers = $viewRepository->getUniqueViewerCount($product, $last30);
        $viewsByDay = $viewRepository->getViewsByDay($product, 30);

        return $this->json([
            'success' => true,
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getProductName(),
            ],
            'stats' => [
                'totalViews' => $viewCount,
                'uniqueViewers' => $uniqueViewers,
                'avgViewDuration' => 0, // Calculate from database
                'viewTrend' => array_map(fn($item) => [
                    'date' => $item['date'],
                    'views' => $item['count'],
                ], $viewsByDay),
            ],
        ]);
    }

    /**
     * Get top products (admin dashboard)
     */
    #[Route('/top-products', name: '_top_products', methods: ['GET'])]
    public function getTopProducts(ProductViewRepository $viewRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $topProducts = $viewRepository->findMostViewed(10);

        return $this->json([
            'success' => true,
            'products' => array_map(fn($item) => [
                'id' => $item['product']->getId(),
                'name' => $item['product']->getProductName(),
                'views' => $item['view_count'],
            ], $topProducts),
        ]);
    }
}
