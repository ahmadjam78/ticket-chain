<?php

namespace App\Domains\Ticket\Events;

use App\Domains\Ticket\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class TicketCreated
 *
 * Event dispatched when a new ticket is created.
 * Used to trigger listeners such as sending notifications or logging.
 *
 * @package App\Domains\Ticket\Events
 */
class TicketCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The ticket instance that was created.
     *
     * @var Ticket
     */
    public Ticket $ticket;

    /**
     * Create a new event instance.
     *
     * @param Ticket $ticket The created ticket.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }
}
