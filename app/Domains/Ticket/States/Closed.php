<?php

namespace App\Domains\Ticket\States;

/**
 * Class Closed
 *
 * Represents the final "Closed" state of a ticket.
 * Indicates that the ticket has been successfully resolved and closed.
 * This is a terminal state, meaning no further transitions are allowed.
 *
 * @package App\Domains\Ticket\States
 */
class Closed extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Closed';
    }

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool Returns true as this is a terminal state.
     */
    public function isFinal(): bool
    {
        return true;
    }

    /**
     * Get the color associated with the state for UI representation.
     *
     * @return string CSS color name or hex code.
     */
    public function color(): string
    {
        return 'gray';
    }
}
