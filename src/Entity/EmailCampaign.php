<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'email_campaign')]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['created_at'])]
class EmailCampaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 50)]
    private string $type; // newsletter, promotional, transactional, abandoned_cart

    #[ORM\Column(type: 'text')]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'draft'; // draft, scheduled, sent, paused

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: 'integer')]
    private int $totalRecipients = 0;

    #[ORM\Column(type: 'integer')]
    private int $sentCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $openCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $clickCount = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getScheduledAt(): ?\DateTimeImmutable { return $this->scheduledAt; }
    public function setScheduledAt(?\DateTimeImmutable $date): self { $this->scheduledAt = $date; return $this; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function setSentAt(?\DateTimeImmutable $date): self { $this->sentAt = $date; return $this; }
    public function getTotalRecipients(): int { return $this->totalRecipients; }
    public function setTotalRecipients(int $count): self { $this->totalRecipients = $count; return $this; }
    public function getSentCount(): int { return $this->sentCount; }
    public function setSentCount(int $count): self { $this->sentCount = $count; return $this; }
    public function getOpenCount(): int { return $this->openCount; }
    public function setOpenCount(int $count): self { $this->openCount = $count; return $this; }
    public function getClickCount(): int { return $this->clickCount; }
    public function setClickCount(int $count): self { $this->clickCount = $count; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getOpenRate(): float { return $this->sentCount > 0 ? ($this->openCount / $this->sentCount) * 100 : 0; }
    public function getClickRate(): float { return $this->sentCount > 0 ? ($this->clickCount / $this->sentCount) * 100 : 0; }
}
