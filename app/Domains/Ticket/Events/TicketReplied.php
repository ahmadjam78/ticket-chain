<?php

namespace App\Domains\Ticket\Events;

use App\Domains\Ticket\Models\TicketMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class TicketReplied
 *
 * Event dispatched when a reply is added to a ticket.
 * Contains the ticket message that was created.
 *
 * @package App\Domains\Ticket\Events
 */
class TicketReplied
{
    use Dispatchable, SerializesModels;

    /**
     * The ticket message instance that was created as the reply.
     *
     * @var TicketMessage
     */
    public TicketMessage $message;

    /**
     * Create a new event instance.
     *
     * @param TicketMessage $message The created reply message.
     */
    public function __construct(TicketMessage $message)
    {
        $this->message = $message;
    }
}
