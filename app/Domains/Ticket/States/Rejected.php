<?php

namespace App\Domains\Ticket\States;

/**
 * Class Rejected
 *
 * Represents the "Rejected" state of a ticket.
 * Indicates that the ticket has been rejected by an admin at either level 1 or level 2.
 * This state is not final, meaning the ticket can transition to other states such as Closed.
 *
 * @package App\Domains\Ticket\States
 */
class Rejected extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Rejected';
    }

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool Returns false as rejected tickets can still be closed or moved to other states.
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
        return 'red';
    }
}
