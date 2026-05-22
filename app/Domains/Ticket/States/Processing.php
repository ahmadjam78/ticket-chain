<?php

namespace App\Domains\Ticket\States;

/**
 * Class Processing
 *
 * Represents the "Processing" state of a ticket.
 * Indicates that the ticket is currently being processed by the system,
 * typically after level 2 approval and before final approval or failure.
 * This state is not final, allowing transitions to Approved, Failed, or other states.
 *
 * @package App\Domains\Ticket\States
 */
class Processing extends TicketState
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    public function label(): string
    {
        return 'Processing';
    }

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool Returns false as processing is an intermediate state.
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
