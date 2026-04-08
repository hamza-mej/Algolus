<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_subscription')]
#[ORM\Index(columns: ['user_id'])]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['next_billing_date'])]
class UserSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: SubscriptionPlan::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SubscriptionPlan $plan;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'active'; // active, trial, paused, cancelled

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $trialEndsAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $nextBillingDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(type: 'integer')]
    private int $renewalCount = 0;

    public function __construct(User $user, SubscriptionPlan $plan)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->startDate = new \DateTimeImmutable();
        
        if ($plan->getTrialDays()) {
            $this->trialEndsAt = $this->startDate->modify("+{$plan->getTrialDays()} days");
            $this->nextBillingDate = $this->trialEndsAt;
            $this->status = 'trial';
        } else {
            $this->nextBillingDate = $this->startDate->modify("+{$plan->getBillingCycleDays()} days");
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getPlan(): SubscriptionPlan { return $this->plan; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getTrialEndsAt(): ?\DateTimeImmutable { return $this->trialEndsAt; }
    public function getNextBillingDate(): \DateTimeImmutable { return $this->nextBillingDate; }
    public function setNextBillingDate(\DateTimeImmutable $date): self { $this->nextBillingDate = $date; return $this; }
    public function getCancelledAt(): ?\DateTimeImmutable { return $this->cancelledAt; }
    public function setCancelledAt(?\DateTimeImmutable $date): self { $this->cancelledAt = $date; return $this; }
    public function getStripeSubscriptionId(): ?string { return $this->stripeSubscriptionId; }
    public function setStripeSubscriptionId(?string $id): self { $this->stripeSubscriptionId = $id; return $this; }
    public function getRenewalCount(): int { return $this->renewalCount; }
    public function setRenewalCount(int $count): self { $this->renewalCount = $count; return $this; }

    public function isActive(): bool { return $this->status === 'active'; }
    public function isTrial(): bool { return $this->status === 'trial'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isTrialExpired(): bool { 
        return $this->trialEndsAt && $this->trialEndsAt < new \DateTimeImmutable();
    }
    public function needsBilling(): bool {
        return $this->nextBillingDate <= new \DateTimeImmutable() && $this->isActive();
    }
}
