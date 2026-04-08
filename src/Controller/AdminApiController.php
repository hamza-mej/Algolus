<?php

namespace App\Controller;

use App\Service\AdminStatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin', name: 'api_admin')]
class AdminApiController extends AbstractController
{
    /**
     * Get dashboard overview
     */
    #[Route('/dashboard', name: '_dashboard', methods: ['GET'])]
    public function getDashboard(AdminStatsService $statsService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $overview = $statsService->getDashboardOverview();

        return $this->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Get sales report
     */
    #[Route('/sales', name: '_sales', methods: ['GET'])]
    public function getSalesReport(AdminStatsService $statsService, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $from = $request->query->has('from')
            ? new \DateTimeImmutable($request->query->get('from'))
            : new \DateTimeImmutable('-30 days');

        $to = $request->query->has('to')
            ? new \DateTimeImmutable($request->query->get('to'))
            : new \DateTimeImmutable();

        $report = $statsService->getSalesReport($from, $to);

        return $this->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get product analytics
     */
    #[Route('/products', name: '_products', methods: ['GET'])]
    public function getProductsAnalytics(AdminStatsService $statsService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $analytics = $statsService->getProductAnalytics();

        return $this->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get user analytics
     */
    #[Route('/users', name: '_users', methods: ['GET'])]
    public function getUsersAnalytics(AdminStatsService $statsService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $analytics = $statsService->getUserAnalytics();

        return $this->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }
}
