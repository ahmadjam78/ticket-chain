<?php

namespace App\Domains\Ticket\Events;

use App\Domains\Ticket\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusChanged
{
    use Dispatchable, SerializesModels;

    public $ticket;
    public $oldStatus;
    public $newStatus;
    public $changedBy; // user who performed the change (optional)

    /**
     * Create a new event instance.
     *
     * @param Ticket $ticket
     * @param string $oldStatus
     * @param string $newStatus
     * @param mixed $changedBy
     */
    public function __construct(Ticket $ticket, $oldStatus, $newStatus, $changedBy = null)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
    }
}
