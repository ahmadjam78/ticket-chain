<?php

namespace App\Domains\Ticket\DTOs;

use App\Shared\DTOs\BaseDTO;

/**
 * Class ReplyTicketDTO
 *
 * Data Transfer Object for replying to an existing support ticket.
 * Encapsulates all required data for adding a reply message,
 * including ticket ID, user ID, message content, and optional attachments.
 *
 * @package App\Domains\Ticket\DTOs
 */
class ReplyTicketDTO extends BaseDTO
{
    /**
     * ReplyTicketDTO constructor.
     *
     * @param int $ticket_id The ID of the ticket being replied to.
     * @param int $user_id The ID of the user creating the reply.
     * @param string $message The reply message text.
     * @param array $attachments Optional array of uploaded files to attach to the reply.
     */
    public function __construct(
        public readonly int $ticket_id,
        public readonly int $user_id,
        public readonly string $message,
        public array $attachments = []
    ) {}
}
