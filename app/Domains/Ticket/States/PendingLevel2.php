<?php

namespace App\Domains\Ticket\States;

/**
 * Class PendingLevel2
 *
 * Represents the "Pending Level 2" state of a ticket.
 * Indicates that the ticket has been approved by level 1 admin and is now awaiting
 * approval or further action from a level 2 admin.
 * This state is not final, allowing transitions to Rejected or Processing.
 *
 * @package App\Domains\Ticket\States
 */
class PendingLevel2 extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Pending Level2';
    }

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool Returns false as this is an intermediate state.
     */
    public function isFinal(): bool
    {
        return false;
    }

    /**
     * Get the color associated with the state for UI representation.
     *
     * @return string CSS color name or hex code.
     */
    public function color(): string
    {
        return 'yellow';
    }
}
