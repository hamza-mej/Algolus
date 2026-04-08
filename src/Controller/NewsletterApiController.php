<?php

namespace App\Controller;

use App\Entity\NewsletterSubscriber;
use App\Repository\NewsletterSubscriberRepository;
use App\Service\EmailCampaignService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/newsletter', name: 'api_newsletter')]
class NewsletterApiController extends AbstractController
{
    /**
     * Subscribe to newsletter
     */
    #[Route('/subscribe', name: '_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, EntityManagerInterface $em, NewsletterSubscriberRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid email'], 400);
        }

        $email = strtolower($email);
        $subscriber = $repo->findOneBy(['email' => $email]);

        if ($subscriber) {
            if ($subscriber->isSubscribed()) {
                return $this->json(['message' => 'Already subscribed'], 200);
            }
            $subscriber->setStatus('subscribed');
        } else {
            $subscriber = new NewsletterSubscriber();
            $subscriber->setEmail($email);
            $subscriber->setStatus('subscribed');
            $em->persist($subscriber);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Successfully subscribed to newsletter',
        ]);
    }

    /**
     * Unsubscribe from newsletter
     */
    #[Route('/unsubscribe/{token}', name: '_unsubscribe', methods: ['GET'])]
    public function unsubscribe(string $token, NewsletterSubscriberRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $subscriber = $repo->findOneBy(['unsubscribeToken' => $token]);

        if (!$subscriber) {
            return $this->json(['error' => 'Invalid token'], 404);
        }

        $subscriber->setStatus('unsubscribed');
        $subscriber->setUnsubscribedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Unsubscribed from newsletter',
        ]);
    }

    /**
     * Get subscriber preferences
     */
    #[Route('/preferences', name: '_preferences', methods: ['GET'])]
    public function getPreferences(NewsletterSubscriberRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $email = $this->getUser()->getEmail();

        $subscriber = $repo->findOneBy(['email' => $email]);

        if (!$subscriber) {
            return $this->json(['error' => 'Not subscribed'], 404);
        }

        return $this->json([
            'email' => $subscriber->getEmail(),
            'status' => $subscriber->getStatus(),
            'preferences' => $subscriber->getPreferences(),
        ]);
    }

    /**
     * Update subscriber preferences
     */
    #[Route('/preferences', name: '_update_preferences', methods: ['POST'])]
    public function updatePreferences(Request $request, NewsletterSubscriberRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $email = $this->getUser()->getEmail();

        $subscriber = $repo->findOneBy(['email' => $email]);

        if (!$subscriber) {
            return $this->json(['error' => 'Not subscribed'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $subscriber->setPreferences($data['preferences'] ?? []);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Preferences updated',
        ]);
    }

    /**
     * Get subscriber count
     */
    #[Route('/stats', name: '_stats', methods: ['GET'])]
    public function getStats(NewsletterSubscriberRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $total = $repo->count(['status' => 'subscribed']);
        $unsubscribed = $repo->count(['status' => 'unsubscribed']);
        $bounced = $repo->count(['status' => 'bounced']);

        return $this->json([
            'subscribed' => $total,
            'unsubscribed' => $unsubscribed,
            'bounced' => $bounced,
        ]);
    }
}
