<?php

namespace App\Controller;

use App\Entity\SupportTicket;
use App\Repository\SupportTicketRepository;
use App\Service\LoyaltyService;
use App\Service\SupportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/loyalty', name: 'api_loyalty')]
class LoyaltyApiController extends AbstractController
{
    /**
     * Get user loyalty account
     */
    #[Route('/account', name: '_account', methods: ['GET'])]
    public function getAccount(LoyaltyService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $loyalty = $service->getLoyaltyAccount($this->getUser());

        return $this->json([
            'currentPoints' => $loyalty->getCurrentPoints(),
            'totalEarned' => $loyalty->getTotalPointsEarned(),
            'totalRedeemed' => $loyalty->getTotalPointsRedeemed(),
            'tier' => $loyalty->getTier(),
            'tierPoints' => $loyalty->getTierPoints(),
            'nextTierThreshold' => $loyalty->getNextTierThreshold(),
            'pointsToNextTier' => $loyalty->getPointsToNextTier(),
            'multiplier' => $loyalty->getTierMultiplier(),
        ]);
    }

    /**
     * Redeem points
     */
    #[Route('/redeem', name: '_redeem', methods: ['POST'])]
    public function redeem(Request $request, LoyaltyService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $data = json_decode($request->getContent(), true);
        $points = $data['points'] ?? 0;

        if ($service->redeemPoints($this->getUser(), $points)) {
            return $this->json([
                'success' => true,
                'message' => "Redeemed {$points} points",
                'discount' => $service->getPointsValue($points),
            ]);
        }

        return $this->json(['error' => 'Insufficient points'], 400);
    }
}

#[Route('/api/support', name: 'api_support')]
class SupportApiController extends AbstractController
{
    /**
     * Create support ticket
     */
    #[Route('/tickets', name: '_create', methods: ['POST'])]
    public function createTicket(Request $request, SupportService $service): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $data = json_decode($request->getContent(), true);

        $ticket = $service->createTicket(
            $this->getUser(),
            $data['subject'] ?? '',
            $data['message'] ?? '',
            $data['category'] ?? 'other',
            $data['priority'] ?? 'normal'
        );

        return $this->json([
            'success' => true,
            'ticketId' => $ticket->getId(),
            'message' => 'Ticket created successfully',
        ]);
    }

    /**
     * Get user tickets
     */
    #[Route('/tickets', name: '_list', methods: ['GET'])]
    public function getTickets(SupportTicketRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $tickets = $repo->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);

        return $this->json([
            'tickets' => array_map(fn($t) => [
                'id' => $t->getId(),
                'subject' => $t->getSubject(),
                'category' => $t->getCategory(),
                'priority' => $t->getPriority(),
                'status' => $t->getStatus(),
                'createdAt' => $t->getCreatedAt(),
                'resolvedAt' => $t->getResolvedAt(),
            ], $tickets),
        ]);
    }

    /**
     * Get ticket details
     */
    #[Route('/tickets/{id}', name: '_detail', methods: ['GET'])]
    public function getTicketDetail(SupportTicket $ticket): JsonResponse
    {
        if ($ticket->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        return $this->json([
            'id' => $ticket->getId(),
            'subject' => $ticket->getSubject(),
            'message' => $ticket->getMessage(),
            'category' => $ticket->getCategory(),
            'priority' => $ticket->getPriority(),
            'status' => $ticket->getStatus(),
            'resolution' => $ticket->getResolution(),
            'createdAt' => $ticket->getCreatedAt(),
            'resolvedAt' => $ticket->getResolvedAt(),
        ]);
    }
}
