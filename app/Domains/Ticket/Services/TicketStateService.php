<?php

namespace App\Domains\Ticket\Services;

use App\Domains\Ticket\Events\TicketStatusChanged;
use App\Domains\Ticket\Jobs\ProcessTicketApproval;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Repositories\TicketMessageRepository;
use App\Domains\Ticket\Repositories\WebServiceLogRepository;
use App\Domains\Ticket\States\Approved;
use App\Domains\Ticket\States\Closed;
use App\Domains\Ticket\States\Failed;
use App\Domains\Ticket\States\Open;
use App\Domains\Ticket\States\PendingLevel2;
use App\Domains\Ticket\States\Processing;
use App\Domains\Ticket\States\Rejected;
use App\Domains\User\Models\User;
use App\Http\Controllers\Api\V1\FakeWebServiceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TicketStateService
 *
 * Manages state transitions for tickets (pending, reject, close).
 * Handles role-based authorization and dispatches events/jobs.
 *
 * @package App\Domains\Ticket\Services
 */
class TicketStateService
{
    protected TicketMessageRepository $messageRepository;
    protected WebServiceLogRepository $logRepository;

    /**
     * TicketStateService constructor.
     *
     * @param TicketMessageRepository $messageRepository Repository for ticket messages
     * @param WebServiceLogRepository $logRepository      Repository for web service logs
     */
    public function __construct(TicketMessageRepository $messageRepository, WebServiceLogRepository $logRepository)
    {
        $this->messageRepository = $messageRepository;
        $this->logRepository = $logRepository;
    }

    /**
     * Move ticket to pending state based on user role.
     *
     * - Level-1 admin → transitions to PendingLevel2
     * - Level-2 admin → transitions to Processing and dispatches approval job
     *
     * @param Ticket $ticket The ticket to update
     * @param User   $user   The user performing the action
     *
     * @return void
     * @throws \Exception If user has insufficient role
     */
    public function pending(Ticket $ticket, User $user): void
    {
        if ($user->hasRole('admin-level-1') && !$user->hasRole('admin-level-2')) {
            DB::transaction(function () use ($ticket) {
                $oldStatus = $ticket->status->getValue();
                $ticket->status->transitionTo(PendingLevel2::class);

                event(new TicketStatusChanged($ticket, $oldStatus, PendingLevel2::class, Auth::id()));
            });
            return;
        }

        if ($user->hasRole('admin-level-2')) {
            $ticket->status->transitionTo(Processing::class);

            // Dispatch async job for processing
            ProcessTicketApproval::dispatch($ticket, $user->id);

            return;
        }

        throw new \Exception('User does not have required role to confirm tickets.');
    }

    /**
     * Reject a ticket with an optional rejection message.
     *
     * Transitions status to Rejected, creates a system message,
     * and dispatches TicketStatusChanged event.
     *
     * @param Ticket      $ticket The ticket to reject
     * @param string|null $text   Optional rejection reason
     *
     * @return void
     */
    public function reject(Ticket $ticket, ?string $text): void
    {
        DB::transaction(function () use ($ticket, $text) {
            $oldStatus = $ticket->status->getValue();
            $ticket->status->transitionTo(Rejected::class);

            $this->messageRepository->create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'message'   => $text,
            ]);

            event(new TicketStatusChanged($ticket, $oldStatus, Rejected::class, Auth::id()));

        });
    }

    /**
     * Close a ticket.
     *
     * Transitions status to Closed and dispatches TicketStatusChanged event.
     *
     * @param Ticket $ticket The ticket to close
     *
     * @return void
     */
    public function close(Ticket $ticket): void
    {
        DB::transaction(function () use ($ticket) {
            $oldStatus = $ticket->status->getValue();
            $ticket->status->transitionTo(Closed::class);

            event(new TicketStatusChanged($ticket, $oldStatus, Closed::class, Auth::id()));

        });
    }
}
