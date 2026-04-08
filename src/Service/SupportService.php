<?php

namespace App\Service;

use App\Entity\SupportTicket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SupportService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    ) {}

    /**
     * Create support ticket
     */
    public function createTicket(User $user, string $subject, string $message, string $category, string $priority = 'normal'): SupportTicket
    {
        $ticket = new SupportTicket();
        $ticket->setUser($user);
        $ticket->setSubject($subject);
        $ticket->setMessage($message);
        $ticket->setCategory($category);
        $ticket->setPriority($priority);

        $this->em->persist($ticket);
        $this->em->flush();

        $this->sendConfirmationEmail($ticket);

        return $ticket;
    }

    /**
     * Resolve ticket
     */
    public function resolveTicket(SupportTicket $ticket, string $resolution): void
    {
        $ticket->setStatus('resolved');
        $ticket->setResolution($resolution);
        $ticket->setResolvedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->sendResolutionEmail($ticket);
    }

    /**
     * Get overdue tickets
     */
    public function getOverdueTickets(): array
    {
        return $this->em->getRepository(SupportTicket::class)
            ->createQueryBuilder('st')
            ->where('st.status != :resolved')
            ->andWhere('st.status != :closed')
            ->setParameter('resolved', 'resolved')
            ->setParameter('closed', 'closed')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get support statistics
     */
    public function getSupportStats(): array
    {
        $stats = $this->em->getRepository(SupportTicket::class)
            ->createQueryBuilder('st')
            ->select('
                COUNT(st.id) as totalTickets,
                SUM(CASE WHEN st.status = :open THEN 1 ELSE 0 END) as openCount,
                SUM(CASE WHEN st.status = :in_progress THEN 1 ELSE 0 END) as inProgressCount,
                SUM(CASE WHEN st.status = :resolved THEN 1 ELSE 0 END) as resolvedCount,
                AVG(EXTRACT(EPOCH FROM (st.resolvedAt - st.createdAt))) as avgResolutionTime
            ')
            ->setParameter('open', 'open')
            ->setParameter('in_progress', 'in_progress')
            ->setParameter('resolved', 'resolved')
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'total' => (int)($stats['totalTickets'] ?? 0),
            'open' => (int)($stats['openCount'] ?? 0),
            'inProgress' => (int)($stats['inProgressCount'] ?? 0),
            'resolved' => (int)($stats['resolvedCount'] ?? 0),
            'avgResolutionTime' => (int)($stats['avgResolutionTime'] ?? 0),
        ];
    }

    private function sendConfirmationEmail(SupportTicket $ticket): void
    {
        try {
            $email = (new Email())
                ->from('support@algolus.com')
                ->to($ticket->getUser()->getEmail())
                ->subject("Support Ticket #{$ticket->getId()} Created")
                ->html("<p>Thank you for contacting support. We'll be in touch soon!</p>");

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error
        }
    }

    private function sendResolutionEmail(SupportTicket $ticket): void
    {
        try {
            $email = (new Email())
                ->from('support@algolus.com')
                ->to($ticket->getUser()->getEmail())
                ->subject("Support Ticket #{$ticket->getId()} Resolved")
                ->html("<p>Your support ticket has been resolved:</p><p>{$ticket->getResolution()}</p>");

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log error
        }
    }
}
