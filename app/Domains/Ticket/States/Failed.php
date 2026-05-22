<?php

namespace App\Domains\Ticket\States;

/**
 * Class Failed
 *
 * Represents the "Failed" state of a ticket.
 * Indicates that the processing of the ticket has failed, typically after
 * multiple retry attempts in the Processing state.
 * This state is not final, allowing transitions to Processing (for retry),
 * Approved (if recovery is possible), Closed, or staying in Failed.
 *
 * @package App\Domains\Ticket\States
 */
class Failed extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Failed';
    }

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool Returns false as failed tickets can be retried or closed.
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
