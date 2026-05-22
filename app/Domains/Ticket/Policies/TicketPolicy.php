<?php

namespace App\Domains\Ticket\Policies;

use App\Domains\User\Models\User;
use App\Domains\Ticket\Models\Ticket;

/**
 * Class TicketPolicy
 *
 * Defines authorization rules for Ticket model operations.
 * Controls access to viewing, creating, replying, closing, updating, and deleting tickets.
 *
 * @package App\Domains\Ticket\Policies
 */
class TicketPolicy
{
    /**
     * Determine if a user can view a specific ticket.
     *
     * Allowed if:
     * - User is an admin, OR
     * - User is the owner of the ticket.
     *
     * @param User   $user   The authenticated user.
     * @param Ticket $ticket The ticket being viewed.
     * @return bool True if authorized, false otherwise.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $ticket->user_id === $user->id;
    }

    /**
     * Determine if a user can create a new ticket.
     *
     * Only customers are allowed to create tickets.
     *
     * @param User $user The authenticated user.
     * @return bool True if authorized, false otherwise.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    /**
     * Determine if a user can reply to a specific ticket.
     *
     * Allowed if:
     * - User is an admin, OR
     * - User is the owner of the ticket.
     *
     * @param User   $user   The authenticated user.
     * @param Ticket $ticket The ticket being replied to.
     * @return bool True if authorized, false otherwise.
     */
    public function reply(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $ticket->user_id === $user->id;
    }

    /**
     * Determine if a user can close a specific ticket.
     *
     * Only admins are allowed to close tickets.
     *
     * @param User   $user   The authenticated user.
     * @param Ticket $ticket The ticket to be closed.
     * @return bool True if authorized, false otherwise.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can update a specific ticket.
     *
     * Only admins are allowed to update tickets.
     *
     * @param User   $user   The authenticated user.
     * @param Ticket $ticket The ticket to be updated.
     * @return bool True if authorized, false otherwise.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can delete a specific ticket.
     *
     * Only admins are allowed to delete tickets.
     *
     * @param User   $user   The authenticated user.
     * @param Ticket $ticket The ticket to be deleted.
     * @return bool True if authorized, false otherwise.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }
}
