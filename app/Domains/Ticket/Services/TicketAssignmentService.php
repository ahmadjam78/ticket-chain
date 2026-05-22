<?php

namespace App\Domains\Ticket\Services;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\User\Models\User;

/**
 * Class TicketAssignmentService
 *
 * Handles the assignment of support tickets to agents.
 * Provides automatic random assignment to eligible admin roles
 * and manual assignment to a specific agent.
 *
 * @package App\Domains\Ticket\Services
 */
class TicketAssignmentService
{
    /**
     * Automatically assign a ticket to a random available agent.
     *
     * Queries users with roles 'admin-level-1' or 'admin-level-2',
     * selects one at random, and assigns the ticket to that agent.
     * If no eligible agent is found, the ticket remains unassigned.
     *
     * @param Ticket $ticket The ticket to be auto-assigned.
     * @return void
     */
    public function autoAssign(Ticket $ticket): void
    {
        $agent = User::role(['admin-level-1', 'admin-level-2'])
            ->inRandomOrder()
            ->first();

        if ($agent) {
            $ticket->update([
                'assigned_to' => $agent->id
            ]);
        }
    }

    /**
     * Manually assign a ticket to a specific agent.
     *
     * Updates the ticket's `assigned_to` field with the given agent's ID.
     *
     * @param Ticket $ticket The ticket to assign.
     * @param User $agent The agent to assign the ticket to.
     * @return Ticket The updated ticket instance.
     */
    public function assignTo(Ticket $ticket, User $agent): Ticket
    {
        $ticket->update([
            'assigned_to' => $agent->id
        ]);

        return $ticket;
    }
}
