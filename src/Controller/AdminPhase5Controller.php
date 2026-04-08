<?php

namespace App\Controller;

use App\Service\InventoryService;
use App\Service\PerformanceMonitorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin', name: 'api_admin')]
class AdminPhase5Controller extends AbstractController
{
    /**
     * Get inventory statistics
     */
    #[Route('/inventory', name: '_inventory', methods: ['GET'])]
    public function getInventoryStats(InventoryService $inventoryService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = $inventoryService->getInventoryStats();

        return $this->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get performance metrics
     */
    #[Route('/performance', name: '_performance', methods: ['GET'])]
    public function getPerformanceMetrics(PerformanceMonitorService $monitor): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $report = $monitor->getReport();

        return $this->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get low stock products
     */
    #[Route('/low-stock', name: '_low_stock', methods: ['GET'])]
    public function getLowStockProducts(InventoryService $inventoryService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $items = $inventoryService->getLowStockProducts();
        $data = array_map(fn($item) => [
            'productId' => $item->getProduct()->getId(),
            'productName' => $item->getProduct()->getProductName(),
            'quantity' => $item->getQuantity(),
            'available' => $item->getAvailable(),
            'minThreshold' => $item->getMinThreshold(),
            'status' => $item->getStatus(),
        ], $items);

        return $this->json([
            'success' => true,
            'lowStockProducts' => $data,
        ]);
    }
}
