<?php

namespace App\Domains\Ticket\Actions;

use App\Domains\Ticket\DTOs\ReplyTicketDTO;
use App\Domains\Ticket\Models\TicketMessage;
use App\Domains\Ticket\Repositories\TicketMessageRepository;

/**
 * Class ReplyTicketAction
 *
 * Handles the creation of a reply message on an existing support ticket.
 * This action encapsulates the logic for persisting a reply and attaching
 * any uploaded files to the message's media collection.
 *
 * @package App\Domains\Ticket\Actions
 */
class ReplyTicketAction
{
    /**
     * ReplyTicketAction constructor.
     *
     * @param TicketMessageRepository $repository The repository used to persist ticket message data.
     */
    public function __construct(
        private TicketMessageRepository $repository
    ) {}

    /**
     * Execute the reply creation process.
     *
     * Creates a new ticket message record for the specified ticket,
     * then attaches any uploaded files to the message's media collection.
     *
     * @param ReplyTicketDTO $dto The data transfer object containing:
     *                            - ticket_id: ID of the ticket being replied to
     *                            - user_id: ID of the user creating the reply
     *                            - message: The reply text content
     *                            - attachments: Array of uploaded files
     * @return TicketMessage The newly created ticket message model instance.
     */
    public function execute(ReplyTicketDTO $dto): TicketMessage
    {
        $message = $this->repository->create([
            'ticket_id' => $dto->ticket_id,
            'user_id'   => $dto->user_id,
            'message'   => $dto->message,
        ]);

        foreach ($dto->attachments as $file) {
            $message
                ->addMedia($file)
                ->toMediaCollection('attachments');
        }

        return $message;
    }
}
