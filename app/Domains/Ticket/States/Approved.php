<?php

namespace App\Domains\Ticket\States;

/**
 * Class Approved
 *
 * Represents the "Approved" state of a ticket.
 * Indicates that the ticket has been successfully approved after processing.
 * This state is not final, allowing transitions to Closed.
 *
 * @package App\Domains\Ticket\States
 */
class Approved extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Approved';
    }

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool Returns false as approved tickets can still be closed.
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
        return 'green';
    }
}
