<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ReviewRepository;
use App\Repository\WishlistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    /**
     * User profile dashboard
     */
    #[Route('', name: 'index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Order history
     */
    #[Route('/orders', name: 'orders')]
    public function orders(OrderRepository $orderRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $this->getUser();

        $orders = $orderRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('profile/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * Order details
     */
    #[Route('/orders/{id}', name: 'order_detail')]
    public function orderDetail(int $id, OrderRepository $orderRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $this->getUser();

        $order = $orderRepo->find($id);
        if (!$order || $order->getUser() !== $user) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('profile/order-detail.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * My reviews
     */
    #[Route('/reviews', name: 'reviews')]
    public function reviews(ReviewRepository $reviewRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $this->getUser();

        $reviews = $reviewRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('profile/reviews.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * My wishlist
     */
    #[Route('/wishlist', name: 'wishlist')]
    public function wishlist(WishlistRepository $wishlistRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $this->getUser();

        $wishlistItems = $wishlistRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('profile/wishlist.html.twig', [
            'wishlist' => $wishlistItems,
        ]);
    }

    /**
     * Settings
     */
    #[Route('/settings', name: 'settings')]
    public function settings(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $this->getUser();

        return $this->render('profile/settings.html.twig', [
            'user' => $user,
        ]);
    }
}
