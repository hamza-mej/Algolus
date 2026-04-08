<?php

namespace App\Controller;

use App\Service\AdvancedAnalyticsService;
use App\Service\SEOService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/analytics', name: 'api_analytics')]
class AnalyticsPhase5Controller extends AbstractController
{
    /**
     * Get comprehensive sales analytics
     */
    #[Route('/sales', name: '_sales', methods: ['GET'])]
    public function getSalesAnalytics(Request $request, AdvancedAnalyticsService $analytics): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $from = $request->query->has('from')
            ? new \DateTimeImmutable($request->query->get('from'))
            : new \DateTimeImmutable('-30 days');

        $to = $request->query->has('to')
            ? new \DateTimeImmutable($request->query->get('to'))
            : new \DateTimeImmutable();

        $data = $analytics->getSalesAnalytics($from, $to);

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get customer lifetime value
     */
    #[Route('/customer-ltv', name: '_customer_ltv', methods: ['GET'])]
    public function getCustomerLTV(AdvancedAnalyticsService $analytics): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $analytics->getCustomerLTV();

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get product performance
     */
    #[Route('/product-performance', name: '_product_performance', methods: ['GET'])]
    public function getProductPerformance(AdvancedAnalyticsService $analytics): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $analytics->getProductPerformance();

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get conversion funnel
     */
    #[Route('/funnel', name: '_funnel', methods: ['GET'])]
    public function getConversionFunnel(AdvancedAnalyticsService $analytics): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $analytics->getConversionFunnel();

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get engagement metrics
     */
    #[Route('/engagement', name: '_engagement', methods: ['GET'])]
    public function getEngagementMetrics(AdvancedAnalyticsService $analytics): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $analytics->getEngagementMetrics();

        return $this->json(['success' => true, 'data' => $data]);
    }

    /**
     * Export analytics to CSV
     */
    #[Route('/export/csv', name: '_export_csv', methods: ['GET'])]
    public function exportCSV(Request $request, AdvancedAnalyticsService $analytics): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $from = new \DateTimeImmutable($request->query->get('from', '-30 days'));
        $to = new \DateTimeImmutable($request->query->get('to', 'now'));

        $csv = $analytics->exportAnalyticsToCSV($from, $to);

        return $this->json([
            'success' => true,
            'data' => $csv,
            'filename' => 'analytics_' . date('Y-m-d') . '.csv',
        ]);
    }

    /**
     * Get SEO audit for product
     */
    #[Route('/seo-audit/{id}', name: '_seo_audit', methods: ['GET'])]
    public function getSEOAudit(int $id, SEOService $seoService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->em->getRepository(\App\Entity\Product::class)->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $audit = $seoService->checkProductSEO($product);

        return $this->json([
            'success' => true,
            'audit' => $audit,
        ]);
    }

    /**
     * Generate product schema
     */
    #[Route('/schema/product/{id}', name: '_schema_product', methods: ['GET'])]
    public function getProductSchema(int $id, SEOService $seoService, Request $request): JsonResponse
    {
        $product = $this->em->getRepository(\App\Entity\Product::class)->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        $schema = $seoService->generateProductSchema($product, $baseUrl);

        return $this->json([
            'success' => true,
            'schema' => $schema,
        ]);
    }

    /**
     * Generate sitemap
     */
    #[Route('/sitemap', name: '_sitemap', methods: ['GET'])]
    public function generateSitemap(SEOService $seoService, Request $request): JsonResponse
    {
        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        $xml = $seoService->generateXMLSitemap($baseUrl);

        return new JsonResponse([
            'success' => true,
            'sitemap' => $xml,
        ]);
    }
}
