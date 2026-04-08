<?php

namespace App\Service;

use App\Entity\WebhookEndpoint;
use App\Entity\WebhookEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private HttpClientInterface $client
    ) {
    }

    /**
     * Register webhook endpoint
     */
    public function registerEndpoint(string $url, array $events): WebhookEndpoint
    {
        $endpoint = new WebhookEndpoint();
        $endpoint->setUrl($url);
        $endpoint->setEvents($events);

        $this->em->persist($endpoint);
        $this->em->flush();

        return $endpoint;
    }

    /**
     * Dispatch webhook event
     */
    public function dispatch(string $eventType, array $payload): void
    {
        // Find all endpoints subscribed to this event
        $endpoints = $this->em->getRepository(WebhookEndpoint::class)
            ->createQueryBuilder('we')
            ->where('we.isActive = true')
            ->getQuery()
            ->getResult();

        foreach ($endpoints as $endpoint) {
            if (in_array($eventType, $endpoint->getEvents())) {
                $event = new WebhookEvent($endpoint, $eventType, $payload);
                $this->em->persist($event);
            }
        }

        $this->em->flush();

        // Send events asynchronously or via queue in production
        $this->processPendingEvents();
    }

    /**
     * Send pending webhook events
     */
    public function processPendingEvents(): void
    {
        $events = $this->em->getRepository(WebhookEvent::class)
            ->createQueryBuilder('we')
            ->where('we.status = :pending')
            ->andWhere('we.attempts < 5')
            ->setParameter('pending', 'pending')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();

        foreach ($events as $event) {
            $this->sendEvent($event);
        }
    }

    /**
     * Send individual webhook event
     */
    private function sendEvent(WebhookEvent $event): void
    {
        try {
            $endpoint = $event->getEndpoint();
            $signature = hash_hmac('sha256', json_encode($event->getPayload()), $endpoint->getSecret());

            $response = $this->client->request('POST', $endpoint->getUrl(), [
                'json' => [
                    'event' => $event->getEventType(),
                    'timestamp' => time(),
                    'data' => $event->getPayload(),
                ],
                'headers' => [
                    'X-Webhook-Signature' => $signature,
                    'X-Event-Type' => $event->getEventType(),
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $event->setStatus('sent');
                $event->setSentAt(new \DateTimeImmutable());
                $endpoint->recordSuccess();
            } else {
                $event->incrementAttempts();
                $event->setLastError('HTTP ' . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            $event->incrementAttempts();
            $event->setLastError($e->getMessage());

            if ($event->getAttempts() >= 5) {
                $event->setStatus('failed');
                $event->getEndpoint()->recordFailure();
            }
        }

        $this->em->flush();
    }

    /**
     * Get endpoint health
     */
    public function getEndpointHealth(WebhookEndpoint $endpoint): array
    {
        $recentEvents = $this->em->getRepository(WebhookEvent::class)
            ->createQueryBuilder('we')
            ->where('we.endpoint = :endpoint')
            ->andWhere('we.createdAt > :since')
            ->setParameter('endpoint', $endpoint)
            ->setParameter('since', new \DateTimeImmutable('-7 days'))
            ->getQuery()
            ->getResult();

        $successful = count(array_filter($recentEvents, fn($e) => $e->getStatus() === 'sent'));
        $failed = count(array_filter($recentEvents, fn($e) => $e->getStatus() === 'failed'));

        return [
            'url' => $endpoint->getUrl(),
            'active' => $endpoint->isActive(),
            'successRate' => round($endpoint->getSuccessRate(), 2),
            'recentEvents' => count($recentEvents),
            'successful' => $successful,
            'failed' => $failed,
            'lastTriggered' => $endpoint->getLastTriggeredAt(),
        ];
    }
}
