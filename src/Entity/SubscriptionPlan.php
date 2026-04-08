<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'subscription_plan')]
#[ORM\Index(columns: ['status'])]
class SubscriptionPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(type: 'string', length: 50)]
    private string $billingCycle; // monthly, quarterly, annual

    #[ORM\Column(type: 'integer')]
    private int $billingCycleDays = 30;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $trialDays = 14;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $setupFee = null;

    #[ORM\Column(type: 'json')]
    private array $features = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $desc): self { $this->description = $desc; return $this; }
    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): self { $this->price = $price; return $this; }
    public function getBillingCycle(): string { return $this->billingCycle; }
    public function setBillingCycle(string $cycle): self { $this->billingCycle = $cycle; return $this; }
    public function getBillingCycleDays(): int { return $this->billingCycleDays; }
    public function setBillingCycleDays(int $days): self { $this->billingCycleDays = $days; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setActive(bool $active): self { $this->isActive = $active; return $this; }
    public function getTrialDays(): ?int { return $this->trialDays; }
    public function setTrialDays(?int $days): self { $this->trialDays = $days; return $this; }
    public function getSetupFee(): ?string { return $this->setupFee; }
    public function setSetupFee(?string $fee): self { $this->setupFee = $fee; return $this; }
    public function getFeatures(): array { return $this->features; }
    public function setFeatures(array $features): self { $this->features = $features; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
