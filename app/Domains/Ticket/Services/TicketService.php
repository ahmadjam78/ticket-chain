<?php

namespace App\Domains\Ticket\Services;

use App\Domains\Ticket\Events\TicketCreated;
use App\Domains\User\Models\User;
use App\Domains\User\Notifications\AdminNotification;
use Illuminate\Support\Facades\DB;
use App\Domains\Ticket\DTOs\CreateTicketDTO;
use App\Domains\Ticket\DTOs\ReplyTicketDTO;
use App\Domains\Ticket\Actions\CreateTicketAction;
use App\Domains\Ticket\Services\TicketReplyService;
use App\Domains\Ticket\Services\TicketAssignmentService;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Models\TicketMessage;

/**
 * Class TicketService
 *
 * Main service for ticket operations including creation and replying.
 * Orchestrates actions, assignment, notifications, and event dispatching.
 *
 * @package App\Domains\Ticket\Services
 */
class TicketService
{
    /**
     * TicketService constructor.
     *
     * @param CreateTicketAction $createAction Action for creating a ticket and its initial message.
     * @param TicketReplyService $replyService Service for handling reply logic with transaction and validation.
     * @param TicketAssignmentService $assignmentService Service for auto-assigning tickets to agents.
     */
    public function __construct(
        protected CreateTicketAction $createAction,
        protected TicketReplyService $replyService,
        protected TicketAssignmentService $assignmentService
    ) {}

    /**
     * Create a new support ticket.
     *
     * Within a database transaction:
     * - Creates ticket and initial message via CreateTicketAction.
     * - Dispatches TicketCreated event.
     * - Auto-assigns the ticket to an available agent.
     * - Notifies all admin-level-2 users about the new ticket.
     *
     * @param CreateTicketDTO $dto Data transfer object containing ticket data.
     * @return Ticket The newly created ticket instance.
     */
    public function create(CreateTicketDTO $dto): Ticket
    {
        return DB::transaction(function () use ($dto) {

            $ticket = $this->createAction->execute($dto);

            TicketCreated::dispatch($ticket);

            $this->assignmentService->autoAssign($ticket);

            $admins = User::role('admin-level-2')->get();

            foreach ($admins as $admin) {
                $admin->notify(new AdminNotification(
                    title: 'New ticket created',
                    message: "User {$ticket->user->name} created a new ticket: '{$ticket->subject}'. Ticket #{$ticket->id}",
                    type: 'info'
                ));
            }

            return $ticket;
        });
    }

    /**
     * Reply to an existing ticket.
     *
     * Sends appropriate notifications based on who is replying:
     * - If $user is provided (admin reply), notify the ticket owner.
     * - If $user is null (customer reply), notify all admin-level-2 users.
     *
     * Delegates the actual reply logic to TicketReplyService.
     *
     * @param ReplyTicketDTO $dto Data transfer object containing reply data.
     * @param Ticket $ticket The ticket being replied to.
     * @param User|null $user The user replying. If null, assumes a customer reply;
     *                        if provided, assumes an admin reply.
     * @return TicketMessage The created reply message.
     */
    public function reply(ReplyTicketDTO $dto, Ticket $ticket, ?User $user = null): TicketMessage
    {
        if (!is_null($user)) {
            // Admin replying: notify all admin-level-2 users
            $admins = User::role('admin-level-2')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminNotification(
                    title: 'New reply on ticket',
                    message: "User {$ticket->user->name} replied to ticket #{$ticket->id} ('{$ticket->subject}').",
                    type: 'info'
                ));
            }
        } else {
            // Customer replying: notify the ticket owner
            $ticket->user->notify(new AdminNotification(
                title: 'New support reply',
                message: "A new reply has been added to your ticket '{$ticket->subject}'. Ticket #{$ticket->id}",
                type: 'success'
            ));
        }

        return $this->replyService->reply($dto);
    }
}
