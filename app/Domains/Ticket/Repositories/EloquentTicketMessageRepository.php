<?php

namespace App\Domains\Ticket\Repositories;

use App\Domains\Ticket\Models\TicketMessage;

/**
 * Class EloquentTicketMessageRepository
 *
 * Eloquent implementation of the TicketMessageRepository interface.
 * Handles database operations for ticket message persistence.
 *
 * @package App\Domains\Ticket\Repositories
 */
class EloquentTicketMessageRepository implements TicketMessageRepository
{
    /**
     * Create a new ticket message record.
     *
     * @param array $data Associative array of message attributes (ticket_id, user_id, message).
     * @return TicketMessage The newly created ticket message instance.
     */
    public function create(array $data): TicketMessage
    {
        return TicketMessage::create($data);
    }
}
