<?php

namespace App\Domains\Ticket\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Abstract base class for ticket states.
 *
 * Defines the state machine for ticket lifecycle including transitions
 * between pending levels, processing, approval, rejection, failure, and closed.
 *
 * @package App\Domains\Ticket\States
 */
abstract class TicketState extends State
{
    /**
     * Get the human-readable label for the state.
     *
     * @return string
     */
    abstract public function label(): string;

    /**
     * Determine if this state is a final (terminal) state.
     *
     * @return bool
     */
    abstract public function isFinal(): bool;

    /**
     * Configure the state machine with allowed transitions.
     *
     * Defines the default state and all possible state transitions
     * based on the ticket workflow.
     *
     * @return StateConfig
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(PendingLevel1::class)

            // Transitions from PendingLevel1 (awaiting level 1 approval)
            ->allowTransition(PendingLevel1::class, PendingLevel2::class)  // Approved by level 1 admin
            ->allowTransition(PendingLevel1::class, Rejected::class)      // Rejected by level 1 admin

            // Transitions from PendingLevel2 (awaiting level 2 approval)
            ->allowTransition(PendingLevel2::class, Rejected::class)      // Rejected by level 2 admin
            ->allowTransition(PendingLevel2::class, Processing::class)    // Moved to processing

            // Transitions from Processing
            ->allowTransition(Processing::class, Approved::class)         // Processing succeeded
            ->allowTransition(Processing::class, Failed::class)           // Processing failed after retries

            // Transitions from Approved / Rejected / Failed to Closed
            ->allowTransition(Approved::class, Closed::class)
            ->allowTransition(Rejected::class, Closed::class)
            ->allowTransition(Failed::class, Failed::class)
            ->allowTransition(Failed::class, Closed::class)
            ->allowTransition(Failed::class, Approved::class)
            ->allowTransition(Failed::class, Processing::class);
    }
}
