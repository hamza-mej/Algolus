<?php

namespace App\Service;

use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Entity\UserSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SubscriptionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {}

    /**
     * Create new subscription for user
     */
    public function createSubscription(User $user, SubscriptionPlan $plan): UserSubscription
    {
        // Cancel existing active subscriptions
        $existing = $this->em->getRepository(UserSubscription::class)
            ->findOneBy(['user' => $user, 'status' => 'active']);

        if ($existing) {
            $existing->setStatus('cancelled');
            $existing->setCancelledAt(new \DateTimeImmutable());
        }

        $subscription = new UserSubscription($user, $plan);
        $this->em->persist($subscription);
        $this->em->flush();

        // Send welcome email
        $this->sendWelcomeEmail($subscription);

        return $subscription;
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(UserSubscription $subscription): void
    {
        $subscription->setStatus('cancelled');
        $subscription->setCancelledAt(new \DateTimeImmutable());
        $this->em->flush();

        $this->sendCancellationEmail($subscription);
    }

    /**
     * Renew subscription after billing date
     */
    public function renewSubscription(UserSubscription $subscription): bool
    {
        if (!$subscription->needsBilling()) {
            return false;
        }

        $plan = $subscription->getPlan();
        $nextBillingDate = new \DateTimeImmutable();
        $nextBillingDate = $nextBillingDate->modify("+{$plan->getBillingCycleDays()} days");

        $subscription->setNextBillingDate($nextBillingDate);
        $subscription->setRenewalCount($subscription->getRenewalCount() + 1);

        // If trial, move to active
        if ($subscription->isTrial()) {
            $subscription->setStatus('active');
        }

        $this->em->flush();

        // Send renewal confirmation
        $this->sendRenewalEmail($subscription);

        return true;
    }

    /**
     * Get all active subscriptions needing billing
     */
    public function getSubscriptionsNeedingBilling(): array
    {
        return $this->em->getRepository(UserSubscription::class)
            ->createQueryBuilder('us')
            ->where('us.nextBillingDate <= :now')
            ->andWhere('us.status IN (:statuses)')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('statuses', ['active', 'trial'])
            ->getQuery()
            ->getResult();
    }

    /**
     * Check and upgrade trial to active
     */
    public function processTrialExpiration(UserSubscription $subscription): void
    {
        if (!$subscription->isTrialExpired()) {
            return;
        }

        if ($subscription->isTrial()) {
            $subscription->setStatus('active');
            $this->renewSubscription($subscription);
        }
    }

    /**
     * Get subscription stats
     */
    public function getSubscriptionStats(): array
    {
        $stats = $this->em->getRepository(UserSubscription::class)
            ->createQueryBuilder('us')
            ->select('
                COUNT(us.id) as totalSubscriptions,
                SUM(CASE WHEN us.status = :active THEN 1 ELSE 0 END) as activeCount,
                SUM(CASE WHEN us.status = :trial THEN 1 ELSE 0 END) as trialCount,
                SUM(CASE WHEN us.status = :cancelled THEN 1 ELSE 0 END) as cancelledCount
            ')
            ->setParameter('active', 'active')
            ->setParameter('trial', 'trial')
            ->setParameter('cancelled', 'cancelled')
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'total' => (int)($stats['totalSubscriptions'] ?? 0),
            'active' => (int)($stats['activeCount'] ?? 0),
            'trial' => (int)($stats['trialCount'] ?? 0),
            'cancelled' => (int)($stats['cancelledCount'] ?? 0),
        ];
    }

    private function sendWelcomeEmail(UserSubscription $subscription): void
    {
        try {
            $email = (new Email())
                ->from('noreply@algolus.com')
                ->to($subscription->getUser()->getEmail())
                ->subject('Welcome to ' . $subscription->getPlan()->getName())
                ->html("<p>Welcome! Your subscription to {$subscription->getPlan()->getName()} is now active.</p>");

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error
        }
    }

    private function sendRenewalEmail(UserSubscription $subscription): void
    {
        try {
            $plan = $subscription->getPlan();
            $email = (new Email())
                ->from('noreply@algolus.com')
                ->to($subscription->getUser()->getEmail())
                ->subject("Subscription Renewed: {$plan->getName()}")
                ->html("<p>Your subscription has been renewed. Thank you for your continued support!</p>");

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error
        }
    }

    private function sendCancellationEmail(UserSubscription $subscription): void
    {
        try {
            $email = (new Email())
                ->from('noreply@algolus.com')
                ->to($subscription->getUser()->getEmail())
                ->subject('Subscription Cancelled')
                ->html("<p>Your subscription has been cancelled. We're sorry to see you go!</p>");

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error
        }
    }
}
