<?php

namespace App\Controller;

use App\Entity\SubscriptionPlan;
use App\Entity\UserSubscription;
use App\Repository\SubscriptionPlanRepository;
use App\Service\SubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/subscriptions', name: 'api_subscriptions')]
class SubscriptionsApiController extends AbstractController
{
    /**
     * Get available plans
     */
    #[Route('/plans', name: '_plans', methods: ['GET'])]
    public function getPlans(SubscriptionPlanRepository $repo): JsonResponse
    {
        $plans = $repo->findBy(['isActive' => true]);

        return $this->json([
            'success' => true,
            'plans' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'description' => $p->getDescription(),
                'price' => $p->getPrice(),
                'billingCycle' => $p->getBillingCycle(),
                'trialDays' => $p->getTrialDays(),
                'setupFee' => $p->getSetupFee(),
                'features' => $p->getFeatures(),
            ], $plans),
        ]);
    }

    /**
     * Get user subscription
     */
    #[Route('/current', name: '_current', methods: ['GET'])]
    public function getCurrentSubscription(EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $subscription = $em->getRepository(UserSubscription::class)
            ->findOneBy(['user' => $this->getUser()]);

        if (!$subscription) {
            return $this->json(['subscription' => null]);
        }

        return $this->json([
            'subscription' => [
                'id' => $subscription->getId(),
                'plan' => $subscription->getPlan()->getName(),
                'status' => $subscription->getStatus(),
                'startDate' => $subscription->getStartDate(),
                'nextBillingDate' => $subscription->getNextBillingDate(),
                'renewalCount' => $subscription->getRenewalCount(),
            ],
        ]);
    }

    /**
     * Subscribe to plan
     */
    #[Route('/subscribe', name: '_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, SubscriptionService $service, SubscriptionPlanRepository $planRepo): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $data = json_decode($request->getContent(), true);
        $planId = $data['planId'] ?? null;

        $plan = $planRepo->find($planId);
        if (!$plan) {
            return $this->json(['error' => 'Plan not found'], 404);
        }

        $subscription = $service->createSubscription($this->getUser(), $plan);

        return $this->json([
            'success' => true,
            'message' => 'Subscribed successfully',
            'subscription' => [
                'id' => $subscription->getId(),
                'status' => $subscription->getStatus(),
            ],
        ]);
    }

    /**
     * Cancel subscription
     */
    #[Route('/cancel', name: '_cancel', methods: ['POST'])]
    public function cancel(SubscriptionService $service, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $subscription = $em->getRepository(UserSubscription::class)
            ->findOneBy(['user' => $this->getUser(), 'status' => 'active']);

        if (!$subscription) {
            return $this->json(['error' => 'No active subscription'], 404);
        }

        $service->cancelSubscription($subscription);

        return $this->json([
            'success' => true,
            'message' => 'Subscription cancelled',
        ]);
    }
}
