<?php

namespace App\Domains\Ticket\Listeners;

use App\Domains\Ticket\Events\TicketStatusChanged;
use App\Domains\User\Notifications\AdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class SendTicketStatusNotification
 *
 * Listener that sends a notification to the ticket owner when the ticket status changes.
 * Implements ShouldQueue to handle the notification asynchronously.
 *
 * @package App\Domains\Ticket\Listeners
 */
class SendTicketStatusNotification implements ShouldQueue
{
    /**
     * Handle the ticket status change event.
     *
     * Sends an appropriate notification to the ticket owner based on the new status.
     *
     * @param TicketStatusChanged $event The event containing ticket, status change details, and user ID.
     * @return void
     */
    public function handle(TicketStatusChanged $event): void
    {
        $ticket = $event->ticket;
        $ticketOwner = $ticket->user;

        if (!$ticketOwner) {
            return;
        }

        // Determine title, message, and notification type based on the new status
        [$title, $message, $type] = $this->getNotificationContent($event);

        $ticketOwner->notify(new AdminNotification($title, $message, $type));
    }

    /**
     * Generate notification content based on the new ticket status.
     *
     * Returns an array containing the title, message body, and notification type.
     *
     * @param TicketStatusChanged $event The event with ticket and status information.
     * @return array{0: string, 1: string, 2: string} [title, message, type]
     */
    protected function getNotificationContent(TicketStatusChanged $event): array
    {
        $ticket = $event->ticket;
        $newStatus = $event->newStatus;

        return match ($newStatus) {
            \App\Domains\Ticket\States\PendingLevel2::class => [
                'Ticket Pending Level2',
                "Your ticket #{$ticket->id} ('{$ticket->subject}') has been marked as pending level2. The support team will review it soon.",
                'warning'
            ],
            \App\Domains\Ticket\States\Approved::class => [
                'Ticket Approved',
                "Your ticket #{$ticket->id} ('{$ticket->subject}') has been approved.",
                'success'
            ],
            \App\Domains\Ticket\States\Failed::class => [
                'Ticket Failed',
                "Your ticket #{$ticket->id} ('{$ticket->subject}') could not be approved and has been marked as failed.",
                'danger'
            ],
            \App\Domains\Ticket\States\Rejected::class => [
                'Ticket Rejected',
                "Your ticket #{$ticket->id} ('{$ticket->subject}') has been rejected.",
                'danger'
            ],
            \App\Domains\Ticket\States\Closed::class => [
                'Ticket Closed',
                "Your ticket #{$ticket->id} ('{$ticket->subject}') has been closed.",
                'success'
            ],
            default => [
                'Ticket Status Changed',
                "Your ticket #{$ticket->id} ('{$ticket->subject}') status changed to {$newStatus}.",
                'info'
            ]
        };
    }
}
