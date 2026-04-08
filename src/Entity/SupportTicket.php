<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'support_ticket')]
#[ORM\Index(columns: ['user_id'])]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
class SupportTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'string', length: 50)]
    private string $category; // billing, shipping, product, account, other

    #[ORM\Column(type: 'string', length: 50)]
    private string $priority = 'normal'; // low, normal, high, urgent

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'open'; // open, in_progress, waiting_customer, resolved, closed

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $resolution = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }
    public function getPriority(): string { return $this->priority; }
    public function setPriority(string $priority): self { $this->priority = $priority; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getResolution(): ?string { return $this->resolution; }
    public function setResolution(?string $resolution): self { $this->resolution = $resolution; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getResolvedAt(): ?\DateTimeImmutable { return $this->resolvedAt; }
    public function setResolvedAt(?\DateTimeImmutable $date): self { $this->resolvedAt = $date; return $this; }

    public function getResponseTime(): ?int
    {
        if (!$this->resolvedAt) {
            return null;
        }
        return $this->resolvedAt->getTimestamp() - $this->createdAt->getTimestamp();
    }

    public function isOverdue(): bool
    {
        $sla = match($this->priority) {
            'urgent' => 2,
            'high' => 8,
            'normal' => 24,
            default => 48,
        };

        $deadline = $this->createdAt->modify("+{$sla} hours");
        return !$this->resolvedAt && $deadline < new \DateTimeImmutable();
    }
}
