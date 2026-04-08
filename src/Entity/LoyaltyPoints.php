<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'loyalty_points')]
#[ORM\Index(columns: ['user_id'])]
class LoyaltyPoints
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'integer')]
    private int $currentPoints = 0;

    #[ORM\Column(type: 'integer')]
    private int $totalPointsEarned = 0;

    #[ORM\Column(type: 'integer')]
    private int $totalPointsRedeemed = 0;

    #[ORM\Column(type: 'string', length: 50)]
    private string $tier = 'bronze'; // bronze, silver, gold, platinum

    #[ORM\Column(type: 'integer')]
    private int $tierPoints = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastTierUpgrade = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getCurrentPoints(): int { return $this->currentPoints; }
    public function setCurrentPoints(int $points): self { $this->currentPoints = max(0, $points); return $this; }
    public function addPoints(int $points): void { $this->currentPoints += $points; $this->totalPointsEarned += $points; }
    public function deductPoints(int $points): bool {
        if ($this->currentPoints >= $points) {
            $this->currentPoints -= $points;
            $this->totalPointsRedeemed += $points;
            return true;
        }
        return false;
    }
    public function getTotalPointsEarned(): int { return $this->totalPointsEarned; }
    public function getTotalPointsRedeemed(): int { return $this->totalPointsRedeemed; }
    public function getTier(): string { return $this->tier; }
    public function setTier(string $tier): self { $this->tier = $tier; return $this; }
    public function getTierPoints(): int { return $this->tierPoints; }
    public function setTierPoints(int $points): self { $this->tierPoints = $points; return $this; }
    public function getLastTierUpgrade(): ?\DateTimeImmutable { return $this->lastTierUpgrade; }
    public function setLastTierUpgrade(?\DateTimeImmutable $date): self { $this->lastTierUpgrade = $date; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function checkTierUpgrade(): bool
    {
        $tiers = ['bronze' => 0, 'silver' => 500, 'gold' => 1500, 'platinum' => 5000];
        $newTier = 'bronze';

        foreach ($tiers as $tier => $points) {
            if ($this->totalPointsEarned >= $points) {
                $newTier = $tier;
            }
        }

        if ($newTier !== $this->tier) {
            $this->tier = $newTier;
            $this->lastTierUpgrade = new \DateTimeImmutable();
            return true;
        }

        return false;
    }

    public function getNextTierThreshold(): int
    {
        $tiers = ['bronze' => 0, 'silver' => 500, 'gold' => 1500, 'platinum' => 5000];
        $current = $tiers[$this->tier] ?? 0;
        
        foreach ($tiers as $tier => $points) {
            if ($points > $current) {
                return $points;
            }
        }

        return $tiers['platinum'];
    }

    public function getPointsToNextTier(): int
    {
        return $this->getNextTierThreshold() - $this->totalPointsEarned;
    }

    public function getTierMultiplier(): float
    {
        return match($this->tier) {
            'silver' => 1.1,
            'gold' => 1.25,
            'platinum' => 1.5,
            default => 1.0,
        };
    }
}
