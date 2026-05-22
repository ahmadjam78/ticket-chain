<?php

namespace App\Domains\Ticket\DTOs;

use App\Shared\DTOs\BaseDTO;

/**
 * Class CreateTicketDTO
 *
 * Data Transfer Object for creating a new support ticket.
 * Encapsulates all required data for ticket creation including
 * user, category, subject, description, priority, and optional attachments.
 *
 * @package App\Domains\Ticket\DTOs
 */
class CreateTicketDTO extends BaseDTO
{
    /**
     * CreateTicketDTO constructor.
     *
     * @param int $user_id The ID of the user creating the ticket.
     * @param int $category_id The ID of the ticket category.
     * @param string $subject The ticket subject/title.
     * @param string $description The initial message/description of the ticket.
     * @param string $priority The priority level of the ticket (e.g., low, medium, high).
     * @param array $attachments Optional array of uploaded files to attach.
     */
    public function __construct(
        public int $user_id,
        public int $category_id,
        public string $subject,
        public string $description,
        public string $priority,
        public array $attachments = []
    ) {}
}
