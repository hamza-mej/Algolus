<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Get unread notifications for user
     */
    public function findUnreadByUser(User $user)
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unread notifications
     */
    public function countUnreadByUser(User $user): int
    {
        return (int)$this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get paginated notifications
     */
    public function findByUser(User $user, int $page = 1, int $limit = 20)
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count all notifications for user
     */
    public function countByUser(User $user): int
    {
        return (int)$this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get notifications by type
     */
    public function findByType(User $user, string $type, int $limit = 10)
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get unsent email notifications
     */
    public function findUnsentEmails(int $limit = 100)
    {
        return $this->createQueryBuilder('n')
            ->where('n.emailSent = false')
            ->orderBy('n.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->leftJoin('n.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(User $user, array $ids = []): int
    {
        $qb = $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', true)
            ->set('n.readAt', ':now')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable());

        if (!empty($ids)) {
            $qb->andWhere('n.id IN (:ids)')
                ->setParameter('ids', $ids);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        $date = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.createdAt < :date')
            ->andWhere('n.isRead = true')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
