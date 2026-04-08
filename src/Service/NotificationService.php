<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private Environment $twig,
        private string $appName = 'Algolus'
    ) {}

    /**
     * Create and send notification
     */
    public function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?Product $product = null,
        ?Order $order = null,
        string $severity = 'info',
        bool $sendEmail = true
    ): Notification {
        $notification = new Notification();
        $notification
            ->setUser($user)
            ->setType($type)
            ->setTitle($title)
            ->setMessage($message)
            ->setActionUrl($actionUrl)
            ->setProduct($product)
            ->setOrder($order)
            ->setSeverity($severity);

        $this->em->persist($notification);
        $this->em->flush();

        if ($sendEmail) {
            $this->sendEmail($notification);
        }

        return $notification;
    }

    /**
     * Review approved notification
     */
    public function notifyReviewApproved(Review $review): Notification
    {
        return $this->notify(
            $review->getUser(),
            'review_approved',
            '⭐ Your review was approved!',
            'Your review for "' . $review->getProduct()->getProductName() . '" has been approved.',
            '/product/' . $review->getProduct()->getId(),
            $review->getProduct(),
            severity: 'success'
        );
    }

    /**
     * Wishlist item on sale notification
     */
    public function notifyWishlistOnSale(Wishlist $wishlist): Notification
    {
        $product = $wishlist->getProduct();
        return $this->notify(
            $wishlist->getUser(),
            'wishlist_sale',
            '🎉 Item on your wishlist is on sale!',
            '"' . $product->getProductName() . '" is now on sale for $' . $product->getProductPrice(),
            '/product/' . $product->getId(),
            $product,
            severity: 'success'
        );
    }

    /**
     * Price drop notification
     */
    public function notifyPriceDrop(Wishlist $wishlist, float $oldPrice, float $newPrice): Notification
    {
        $product = $wishlist->getProduct();
        $savings = $oldPrice - $newPrice;

        return $this->notify(
            $wishlist->getUser(),
            'price_drop',
            '💰 Price dropped on wishlist item!',
            '"' . $product->getProductName() . '" dropped from $' . number_format($oldPrice, 2) . ' to $' . number_format($newPrice, 2) . '. Save $' . number_format($savings, 2),
            '/product/' . $product->getId(),
            $product,
            severity: 'success'
        );
    }

    /**
     * Order shipped notification
     */
    public function notifyOrderShipped(Order $order): Notification
    {
        return $this->notify(
            $order->getUser(),
            'order_shipped',
            '📦 Your order has shipped!',
            'Order #' . $order->getId() . ' is on its way to you.',
            '/orders/' . $order->getId(),
            order: $order,
            severity: 'success'
        );
    }

    /**
     * Order delivered notification
     */
    public function notifyOrderDelivered(Order $order): Notification
    {
        return $this->notify(
            $order->getUser(),
            'order_delivered',
            '✅ Your order has been delivered!',
            'Order #' . $order->getId() . ' has arrived. Please review your purchase.',
            '/orders/' . $order->getId(),
            order: $order,
            severity: 'success'
        );
    }

    /**
     * New product in category notification
     */
    public function notifyNewProduct(Product $product, User $user): Notification
    {
        return $this->notify(
            $user,
            'new_product',
            '🆕 New product in your favorite category!',
            '"' . $product->getProductName() . '" just arrived.',
            '/product/' . $product->getId(),
            $product,
            severity: 'info'
        );
    }

    /**
     * Back in stock notification
     */
    public function notifyBackInStock(Product $product, User $user): Notification
    {
        return $this->notify(
            $user,
            'back_in_stock',
            '✅ Item back in stock!',
            '"' . $product->getProductName() . '" is back in stock now.',
            '/product/' . $product->getId(),
            $product,
            severity: 'success'
        );
    }

    /**
     * Send email for notification
     */
    public function sendEmail(Notification $notification): bool
    {
        try {
            $user = $notification->getUser();

            $email = (new Email())
                ->from('noreply@algolus.com')
                ->to($user->getEmail())
                ->subject($notification->getTitle())
                ->html($this->renderEmailTemplate($notification));

            $this->mailer->send($email);

            $notification->setEmailSent(true);
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send batch emails for unsent notifications
     */
    public function sendUnsendEmailNotifications(int $limit = 100): int
    {
        $repository = $this->em->getRepository(Notification::class);
        $notifications = $repository->findUnsentEmails($limit);

        $sent = 0;
        foreach ($notifications as $notification) {
            if ($this->sendEmail($notification)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Render email template
     */
    private function renderEmailTemplate(Notification $notification): string
    {
        $data = [
            'title' => $notification->getTitle(),
            'message' => $notification->getMessage(),
            'actionUrl' => $notification->getActionUrl(),
            'appName' => $this->appName,
            'type' => $notification->getType(),
        ];

        try {
            return $this->twig->render('emails/notification.html.twig', $data);
        } catch (\Exception $e) {
            return sprintf(
                '<p>%s</p><p>%s</p>%s',
                $notification->getTitle(),
                $notification->getMessage(),
                $notification->getActionUrl()
                    ? '<p><a href="' . $notification->getActionUrl() . '">View</a></p>'
                    : ''
            );
        }
    }
}
