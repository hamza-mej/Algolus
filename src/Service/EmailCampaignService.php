<?php

namespace App\Service;

use App\Entity\EmailCampaign;
use App\Entity\NewsletterSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailCampaignService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private EntityManagerInterface $em,
        private string $senderEmail = 'noreply@algolus.com',
        private string $senderName = 'Algolus'
    ) {}

    /**
     * Send newsletter campaign
     */
    public function sendNewsletter(EmailCampaign $campaign, ?int $limit = null): int
    {
        if ($campaign->getStatus() === 'sent') {
            return 0;
        }

        $subscribers = $this->em->getRepository(NewsletterSubscriber::class)
            ->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'subscribed')
            ->setMaxResults($limit ?? 1000)
            ->getQuery()
            ->getResult();

        $sent = 0;
        foreach ($subscribers as $subscriber) {
            if ($this->sendCampaignEmail($campaign, $subscriber)) {
                $sent++;
            }
        }

        // Update campaign stats
        $campaign->setSentCount($sent);
        $campaign->setSentAt(new \DateTimeImmutable());
        $campaign->setStatus('sent');
        $this->em->flush();

        return $sent;
    }

    /**
     * Send single campaign email
     */
    private function sendCampaignEmail(EmailCampaign $campaign, NewsletterSubscriber $subscriber): bool
    {
        try {
            $unsubscribeUrl = "/newsletter/unsubscribe/" . $subscriber->getUnsubscribeToken();
            
            $html = $campaign->getContent();
            $html .= '<br><a href="' . $unsubscribeUrl . '" style="font-size: 12px; color: #999;">Unsubscribe</a>';

            $email = (new Email())
                ->from("{$this->senderName} <{$this->senderEmail}>")
                ->to($subscriber->getEmail())
                ->subject($campaign->getSubject())
                ->html($html);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send transactional email
     */
    public function sendTransactional(string $templateName, array $context, string $to, string $subject): bool
    {
        try {
            $html = $this->twig->render("emails/{$templateName}.html.twig", $context);

            $email = (new Email())
                ->from("{$this->senderName} <{$this->senderEmail}>")
                ->to($to)
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send abandoned cart email
     */
    public function sendAbandonedCartEmail(\App\Entity\User $user, array $cartItems): bool
    {
        $total = array_reduce($cartItems, fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);

        return $this->sendTransactional('abandoned_cart', [
            'user' => $user,
            'cartItems' => $cartItems,
            'total' => $total,
            'recoveryUrl' => '/cart/recover',
        ], $user->getEmail(), 'Complete your purchase - items waiting in cart');
    }

    /**
     * Send product review request email
     */
    public function sendReviewRequestEmail(\App\Entity\User $user, \App\Entity\Product $product): bool
    {
        return $this->sendTransactional('review_request', [
            'user' => $user,
            'product' => $product,
            'reviewUrl' => "/product/{$product->getId()}#reviews",
        ], $user->getEmail(), 'Share your thoughts on ' . $product->getProductName());
    }

    /**
     * Send price drop notification
     */
    public function sendPriceDropEmail(\App\Entity\User $user, \App\Entity\Product $product, float $oldPrice, float $newPrice): bool
    {
        return $this->sendTransactional('price_drop', [
            'user' => $user,
            'product' => $product,
            'oldPrice' => $oldPrice,
            'newPrice' => $newPrice,
            'savings' => $oldPrice - $newPrice,
            'productUrl' => "/product/{$product->getId()}",
        ], $user->getEmail(), 'Price drop! ' . $product->getProductName());
    }

    /**
     * Send back in stock notification
     */
    public function sendBackInStockEmail(\App\Entity\User $user, \App\Entity\Product $product): bool
    {
        return $this->sendTransactional('back_in_stock', [
            'user' => $user,
            'product' => $product,
            'productUrl' => "/product/{$product->getId()}",
        ], $user->getEmail(), $product->getProductName() . ' is back in stock!');
    }

    /**
     * Get campaign statistics
     */
    public function getCampaignStats(EmailCampaign $campaign): array
    {
        return [
            'totalRecipients' => $campaign->getTotalRecipients(),
            'sent' => $campaign->getSentCount(),
            'opens' => $campaign->getOpenCount(),
            'clicks' => $campaign->getClickCount(),
            'openRate' => round($campaign->getOpenRate(), 2),
            'clickRate' => round($campaign->getClickRate(), 2),
            'deliveryRate' => $campaign->getTotalRecipients() > 0 
                ? round(($campaign->getSentCount() / $campaign->getTotalRecipients()) * 100, 2)
                : 0,
        ];
    }
}
