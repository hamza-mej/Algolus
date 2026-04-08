<?php

namespace App\Controller;

use App\Entity\ABTest;
use App\Entity\WebhookEndpoint;
use App\Repository\ABTestRepository;
use App\Service\WebhookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/ab-tests', name: 'api_ab_tests')]
class ABTestApiController extends AbstractController
{
    /**
     * List A/B tests
     */
    #[Route('', name: '_list', methods: ['GET'])]
    public function listTests(ABTestRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tests = $repo->findAll();

        return $this->json([
            'tests' => array_map(fn($t) => [
                'id' => $t->getId(),
                'name' => $t->getName(),
                'type' => $t->getType(),
                'status' => $t->getStatus(),
                'metric' => $t->getMetric(),
                'variantARate' => round($t->getVariantAConversionRate(), 2),
                'variantBRate' => round($t->getVariantBConversionRate(), 2),
                'improvement' => round($t->getImprovement(), 2),
                'winner' => $t->getWinner(),
                'startDate' => $t->getStartDate(),
                'endDate' => $t->getEndDate(),
            ], $tests),
        ]);
    }

    /**
     * Create A/B test
     */
    #[Route('', name: '_create', methods: ['POST'])]
    public function createTest(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        $test = new ABTest();
        $test->setName($data['name'] ?? '');
        $test->setDescription($data['description'] ?? '');
        $test->setType($data['type'] ?? '');
        $test->setVariantA($data['variantA'] ?? []);
        $test->setVariantB($data['variantB'] ?? []);
        $test->setSplitPercentage((float)($data['splitPercentage'] ?? 50));
        $test->setMetric($data['metric'] ?? 'conversion_rate');

        $em->persist($test);
        $em->flush();

        return $this->json([
            'success' => true,
            'testId' => $test->getId(),
        ]);
    }

    /**
     * Get test details
     */
    #[Route('/{id}', name: '_detail', methods: ['GET'])]
    public function getTest(ABTest $test): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json([
            'id' => $test->getId(),
            'name' => $test->getName(),
            'description' => $test->getDescription(),
            'type' => $test->getType(),
            'status' => $test->getStatus(),
            'metric' => $test->getMetric(),
            'variantA' => array_merge(
                $test->getVariantA(),
                ['views' => $test->getVariantAConversionRate(), 'conversions' => $test->getVariantAConversionRate()]
            ),
            'variantB' => array_merge(
                $test->getVariantB(),
                ['views' => $test->getVariantBConversionRate(), 'conversions' => $test->getVariantBConversionRate()]
            ),
            'improvement' => round($test->getImprovement(), 2),
            'winner' => $test->getWinner(),
        ]);
    }

    /**
     * End test
     */
    #[Route('/{id}/end', name: '_end', methods: ['POST'])]
    public function endTest(ABTest $test, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $test->setStatus('completed');
        $test->setEndDate(new \DateTimeImmutable());
        $test->setWinner($test->determineWinner());

        $em->flush();

        return $this->json([
            'success' => true,
            'winner' => $test->getWinner(),
        ]);
    }
}

#[Route('/api/webhooks', name: 'api_webhooks')]
class WebhookApiController extends AbstractController
{
    /**
     * Register webhook
     */
    #[Route('', name: '_register', methods: ['POST'])]
    public function register(Request $request, WebhookService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        $endpoint = $service->registerEndpoint(
            $data['url'] ?? '',
            $data['events'] ?? []
        );

        return $this->json([
            'success' => true,
            'endpointId' => $endpoint->getId(),
            'secret' => $endpoint->getSecret(),
        ]);
    }

    /**
     * Get webhook health
     */
    #[Route('/{id}/health', name: '_health', methods: ['GET'])]
    public function getHealth(WebhookEndpoint $endpoint, WebhookService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $health = $service->getEndpointHealth($endpoint);

        return $this->json(['health' => $health]);
    }
}
