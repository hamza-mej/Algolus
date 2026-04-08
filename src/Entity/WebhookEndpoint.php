<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'webhook_endpoint')]
#[ORM\Index(columns: ['is_active'])]
class WebhookEndpoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'json')]
    private array $events = [];

    #[ORM\Column(type: 'string', length: 100)]
    private string $secret;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    private int $successCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $failureCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastTriggeredAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->secret = bin2hex(random_bytes(32));
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): self { $this->url = $url; return $this; }
    public function getEvents(): array { return $this->events; }
    public function setEvents(array $events): self { $this->events = $events; return $this; }
    public function getSecret(): string { return $this->secret; }
    public function isActive(): bool { return $this->isActive; }
    public function setActive(bool $active): self { $this->isActive = $active; return $this; }
    public function getSuccessCount(): int { return $this->successCount; }
    public function recordSuccess(): void { $this->successCount++; $this->lastTriggeredAt = new \DateTimeImmutable(); }
    public function getFailureCount(): int { return $this->failureCount; }
    public function recordFailure(): void { $this->failureCount++; }
    public function getLastTriggeredAt(): ?\DateTimeImmutable { return $this->lastTriggeredAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getSuccessRate(): float
    {
        $total = $this->successCount + $this->failureCount;
        return $total > 0 ? ($this->successCount / $total) * 100 : 0;
    }
}

#[ORM\Entity]
#[ORM\Table(name: 'webhook_event')]
#[ORM\Index(columns: ['endpoint_id'])]
#[ORM\Index(columns: ['event_type'])]
#[ORM\Index(columns: ['created_at'])]
class WebhookEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WebhookEndpoint::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WebhookEndpoint $endpoint;

    #[ORM\Column(type: 'string', length: 100)]
    private string $eventType; // order.created, order.shipped, product.updated, etc

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'pending'; // pending, sent, failed

    #[ORM\Column(type: 'integer')]
    private int $attempts = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    public function __construct(WebhookEndpoint $endpoint, string $eventType, array $payload)
    {
        $this->endpoint = $endpoint;
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getEndpoint(): WebhookEndpoint { return $this->endpoint; }
    public function getEventType(): string { return $this->eventType; }
    public function getPayload(): array { return $this->payload; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getAttempts(): int { return $this->attempts; }
    public function incrementAttempts(): void { $this->attempts++; }
    public function getLastError(): ?string { return $this->lastError; }
    public function setLastError(?string $error): self { $this->lastError = $error; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function setSentAt(?\DateTimeImmutable $date): self { $this->sentAt = $date; return $this; }
}
