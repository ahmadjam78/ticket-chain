<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\Repositories\TicketRepository;
use App\Domains\Ticket\DTOs\CreateTicketDTO;
use App\Domains\Ticket\Models\Ticket;

/**
 * Class CreateTicketAction
 *
 * Handles the creation of a new support ticket along with its initial message
 * and associated file attachments. Follows the action pattern to encapsulate
 * business logic for ticket creation.
 *
 * @package App\Domains\Ticket\Actions
 */
class CreateTicketAction
{
    /**
     * CreateTicketAction constructor.
     *
     * @param TicketRepository $repository The repository used to persist ticket data.
     */
    public function __construct(
        private TicketRepository $repository
    ) {}

    /**
     * Execute the ticket creation process.
     *
     * Creates a new ticket record, creates the initial message for the ticket,
     * and attaches any uploaded files to the message's media collection.
     *
     * @param CreateTicketDTO $dto The data transfer object containing ticket data,
     *                             including user_id, category_id, subject, priority,
     *                             description, and attachments.
     * @return Ticket The newly created ticket model instance.
     */
    public function execute(CreateTicketDTO $dto): Ticket
    {
        $ticket = $this->repository->create([
            'user_id'     => $dto->user_id,
            'category_id' => $dto->category_id,
            'subject'     => $dto->subject,
            'priority'    => $dto->priority,
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $dto->user_id,
            'message' => $dto->description,
        ]);

        foreach ($dto->attachments as $file) {
            $message
                ->addMedia($file)
                ->toMediaCollection('attachments');
        }

        return $ticket;
    }
}
