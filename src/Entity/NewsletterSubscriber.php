<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'newsletter_subscriber')]
#[ORM\Index(columns: ['email'])]
#[ORM\Index(columns: ['status'])]
class NewsletterSubscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'subscribed'; // subscribed, unsubscribed, bounced

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $subscribedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $unsubscribedAt = null;

    #[ORM\Column(type: 'json')]
    private array $preferences = [];

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $unsubscribeToken;

    public function __construct()
    {
        $this->subscribedAt = new \DateTimeImmutable();
        $this->unsubscribeToken = bin2hex(random_bytes(18));
    }

    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = strtolower($email); return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getSubscribedAt(): \DateTimeImmutable { return $this->subscribedAt; }
    public function getUnsubscribedAt(): ?\DateTimeImmutable { return $this->unsubscribedAt; }
    public function setUnsubscribedAt(?\DateTimeImmutable $date): self { $this->unsubscribedAt = $date; return $this; }
    public function getPreferences(): array { return $this->preferences; }
    public function setPreferences(array $prefs): self { $this->preferences = $prefs; return $this; }
    public function getUnsubscribeToken(): string { return $this->unsubscribeToken; }
    public function isSubscribed(): bool { return $this->status === 'subscribed'; }
}
