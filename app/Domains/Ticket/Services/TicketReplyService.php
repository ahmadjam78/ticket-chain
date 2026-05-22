<?php

namespace App\Domains\Ticket\Services;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Events\TicketReplied;
use App\Domains\Ticket\DTOs\ReplyTicketDTO;
use App\Domains\Ticket\Actions\ReplyTicketAction;
use Illuminate\Support\Facades\DB;
use DomainException;

/**
 * Class TicketReplyService
 *
 * Handles the business logic for replying to a support ticket.
 * Ensures business rules (e.g., cannot reply to closed tickets),
 * manages transaction integrity, and dispatches domain events.
 *
 * @package App\Domains\Ticket\Services
 */
class TicketReplyService
{
    /**
     * TicketReplyService constructor.
     *
     * @param ReplyTicketAction $replyAction The action responsible for persisting the reply message and attachments.
     */
    public function __construct(
        private ReplyTicketAction $replyAction
    ) {}

    /**
     * Process a reply to a ticket.
     *
     * Performs the following operations within a database transaction:
     * - Locks the ticket row for update to prevent concurrent modifications.
     * - Validates that the ticket is not closed.
     * - Creates the reply message via ReplyTicketAction.
     * - Automatically reopens the ticket if it was in 'resolved' state.
     * - Dispatches a TicketReplied event after the transaction is committed.
     *
     * @param ReplyTicketDTO $dto The data transfer object containing ticket_id, user_id, message, and attachments.
     * @return \App\Domains\Ticket\Models\TicketMessage The created message instance with the 'user' relationship loaded.
     * @throws \DomainException If the ticket is closed, preventing a reply.
     */
    public function reply(ReplyTicketDTO $dto)
    {
        return DB::transaction(function () use ($dto) {

            // Lock ticket row
            $ticket = Ticket::lockForUpdate()
                ->findOrFail($dto->ticket_id);

            // Business rule
            if ($ticket->status === 'closed') {
                throw new DomainException(
                    'Cannot reply to a closed ticket.'
                );
            }

            // Create message
            $message = $this->replyAction->execute($dto);

            // Reopen if needed
            if ($ticket->status === 'resolved') {
                $ticket->update(['status' => 'open']);
            }

            // Dispatch domain event AFTER commit
            DB::afterCommit(function () use ($message) {
                TicketReplied::dispatch($message);
            });

            return $message->load('user');
        });
    }
}
