<?php

namespace App\Domains\Ticket\States;

/**
 * Class PendingLevel1
 *
 * Represents the initial "Pending Level 1" state of a ticket.
 * Indicates that the ticket has been created by a customer and is awaiting
 * approval or review from a level 1 admin.
 * This state is not final, allowing transitions to PendingLevel2 or Rejected.
 *
 * @package App\Domains\Ticket\States
 */
class PendingLevel1 extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Pending Level1';
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
