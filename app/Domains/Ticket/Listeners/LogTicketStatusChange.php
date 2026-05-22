<?php

namespace App\Domains\Ticket\Listeners;

use App\Domains\Ticket\Events\TicketStatusChanged;
use App\Domains\Ticket\Models\TicketStatusLog;
use Illuminate\Support\Facades\Log;

/**
 * Class LogTicketStatusChange
 *
 * Listener for the TicketStatusChanged event.
 * Creates a log entry in the ticket_status_logs table
 * recording the status transition.
 *
 * @package App\Domains\Ticket\Listeners
 */
class LogTicketStatusChange
{
    /**
     * Handle the ticket status change event.
     *
     * @param TicketStatusChanged $event The event containing ticket, old status, new status, and user ID.
     * @return void
     */
    public function handle(TicketStatusChanged $event): void
    {
        $ticket = $event->ticket;
        $old = $event->oldStatus;
        $new = $event->newStatus;
        $user = $event->changedBy;

        // Build a log message (useful for debugging or system logs)
        $logMessage = sprintf(
            "Ticket #%d (%s) status changed from '%s' to '%s' by %s (ID: %s)",
            $ticket->id,
            $ticket->subject,
            $old,
            $new,
            'system',
            $user ?? 'N/A'
        );

        // Persist the status change to the database
        TicketStatusLog::create([
            'ticket_id'   => $event->ticket->id,
            'old_status'  => $event->oldStatus,
            'new_status'  => $event->newStatus,
            'changed_by'  => $event->changedBy,
        ]);
    }
}
