<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/notifications', name: 'api_notifications')]
class NotificationsApiController extends AbstractController
{
    /**
     * Get user's notifications
     */
    #[Route('', name: '_get', methods: ['GET'])]
    public function getNotifications(
        NotificationRepository $repo,
        Request $request
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $page = max(1, (int)$request->query->get('page', 1));
        $unreadOnly = $request->query->getBoolean('unread', false);

        if ($unreadOnly) {
            $notifications = $repo->findUnreadByUser($user);
            $total = count($notifications);
        } else {
            $notifications = $repo->findByUser($user, $page, 20);
            $total = $repo->countByUser($user);
        }

        return $this->json([
            'success' => true,
            'count' => count($notifications),
            'unreadCount' => $repo->countUnreadByUser($user),
            'notifications' => array_map(fn($n) => [
                'id' => $n->getId(),
                'type' => $n->getType(),
                'title' => $n->getTitle(),
                'message' => $n->getMessage(),
                'severity' => $n->getSeverity(),
                'actionUrl' => $n->getActionUrl(),
                'isRead' => $n->isRead(),
                'createdAt' => $n->getCreatedAt()->format('Y-m-d H:i'),
            ], $notifications),
            'pagination' => [
                'page' => $page,
                'total' => ceil($total / 20),
                'per_page' => 20,
            ],
        ]);
    }

    /**
     * Get unread count
     */
    #[Route('/count', name: '_count', methods: ['GET'])]
    public function getUnreadCount(NotificationRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        return $this->json([
            'unreadCount' => $repo->countUnreadByUser($user),
        ]);
    }

    /**
     * Mark as read
     */
    #[Route('/read', name: '_read', methods: ['POST'])]
    public function markAsRead(
        Request $request,
        NotificationRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        $marked = $repo->markAsRead($user, $ids);

        return $this->json([
            'success' => true,
            'marked' => $marked,
            'unreadCount' => $repo->countUnreadByUser($user),
        ]);
    }

    /**
     * Mark single notification as read
     */
    #[Route('/{id}/read', name: '_mark_read', methods: ['POST'])]
    public function markOneAsRead(
        Notification $notification,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($notification->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $notification->setIsRead(true);
        $em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    #[Route('/{id}', name: '_delete', methods: ['DELETE'])]
    public function deleteNotification(
        Notification $notification,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($notification->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $em->remove($notification);
        $em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Delete all notifications
     */
    #[Route('/clear-all', name: '_clear_all', methods: ['POST'])]
    public function clearAllNotifications(
        NotificationRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $notifications = $repo->findByUser($user, 1, 1000);

        foreach ($notifications as $n) {
            $em->remove($n);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'All notifications deleted',
        ]);
    }
}
