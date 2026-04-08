<?php

namespace App\Service;

use App\Entity\LoyaltyPoints;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class LoyaltyService
{
    private int $pointsPerDollar = 1; // 1 point per $1 spent

    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Award points for purchase
     */
    public function awardPurchasePoints(User $user, float $amount): LoyaltyPoints
    {
        $loyalty = $this->getLoyaltyAccount($user);
        
        $points = (int)($amount * $this->pointsPerDollar);
        $multiplier = $loyalty->getTierMultiplier();
        $earnedPoints = (int)($points * $multiplier);

        $loyalty->addPoints($earnedPoints);
        $loyalty->checkTierUpgrade();

        $this->em->flush();

        return $loyalty;
    }

    /**
     * Award bonus points (referral, review, etc.)
     */
    public function awardBonusPoints(User $user, int $points, string $reason = ''): LoyaltyPoints
    {
        $loyalty = $this->getLoyaltyAccount($user);
        $loyalty->addPoints($points);
        $loyalty->checkTierUpgrade();

        $this->em->flush();

        return $loyalty;
    }

    /**
     * Redeem points for discount
     */
    public function redeemPoints(User $user, int $points): bool
    {
        $loyalty = $this->getLoyaltyAccount($user);

        // 100 points = $1 discount
        $minPoints = 100;
        if ($points < $minPoints) {
            return false;
        }

        return $loyalty->deductPoints($points);
    }

    /**
     * Get loyalty account or create if not exists
     */
    public function getLoyaltyAccount(User $user): LoyaltyPoints
    {
        $loyalty = $this->em->getRepository(LoyaltyPoints::class)
            ->findOneBy(['user' => $user]);

        if (!$loyalty) {
            $loyalty = new LoyaltyPoints($user);
            $this->em->persist($loyalty);
            $this->em->flush();
        }

        return $loyalty;
    }

    /**
     * Get points value in dollars
     */
    public function getPointsValue(int $points): float
    {
        // 100 points = $1
        return $points / 100;
    }

    /**
     * Get tier multiplier
     */
    public function getTierMultiplier(User $user): float
    {
        return $this->getLoyaltyAccount($user)->getTierMultiplier();
    }

    /**
     * Get user tier
     */
    public function getUserTier(User $user): string
    {
        return $this->getLoyaltyAccount($user)->getTier();
    }

    /**
     * Get loyalty statistics
     */
    public function getLoyaltyStats(): array
    {
        $stats = $this->em->getRepository(LoyaltyPoints::class)
            ->createQueryBuilder('lp')
            ->select('
                COUNT(lp.id) as totalMembers,
                SUM(CASE WHEN lp.tier = :bronze THEN 1 ELSE 0 END) as bronzeCount,
                SUM(CASE WHEN lp.tier = :silver THEN 1 ELSE 0 END) as silverCount,
                SUM(CASE WHEN lp.tier = :gold THEN 1 ELSE 0 END) as goldCount,
                SUM(CASE WHEN lp.tier = :platinum THEN 1 ELSE 0 END) as platinumCount,
                SUM(lp.currentPoints) as totalPointsOutstanding,
                AVG(lp.currentPoints) as avgPointsPerUser
            ')
            ->setParameter('bronze', 'bronze')
            ->setParameter('silver', 'silver')
            ->setParameter('gold', 'gold')
            ->setParameter('platinum', 'platinum')
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'totalMembers' => (int)($stats['totalMembers'] ?? 0),
            'tierBreakdown' => [
                'bronze' => (int)($stats['bronzeCount'] ?? 0),
                'silver' => (int)($stats['silverCount'] ?? 0),
                'gold' => (int)($stats['goldCount'] ?? 0),
                'platinum' => (int)($stats['platinumCount'] ?? 0),
            ],
            'pointsOutstanding' => (int)($stats['totalPointsOutstanding'] ?? 0),
            'avgPointsPerUser' => round((float)($stats['avgPointsPerUser'] ?? 0), 2),
        ];
    }
}
