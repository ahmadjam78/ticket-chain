<?php

namespace App\Domains\Ticket\Repositories;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\States\Approved;
use App\Domains\Ticket\States\Closed;
use App\Domains\Ticket\States\Failed;
use App\Domains\Ticket\States\PendingLevel1;
use App\Domains\Ticket\States\PendingLevel2;
use App\Domains\Ticket\States\Rejected;
use App\Shared\Enums\TicketStatus;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class EloquentTicketRepository
 *
 * Eloquent implementation of the TicketRepository interface.
 * Handles ticket data operations with support for filtering, sorting,
 * pagination, and role-based access control.
 *
 * @package App\Domains\Ticket\Repositories
 */
class EloquentTicketRepository implements TicketRepository
{
    /**
     * Retrieve paginated list of tickets for a specific user.
     *
     * For non-admin users, only returns tickets owned by the user.
     * For admin users, returns all tickets.
     *
     * @param \App\Domains\User\Models\User $user The authenticated user.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Paginated tickets (10 per page).
     */
    public function paginateForUser($user)
    {
        $query = QueryBuilder::for(Ticket::class)
            ->allowedFilters(['priority'])
            ->allowedSorts(['created_at'])
            ->latest()
            ->with('user');

        // Restrict to user's own tickets if not an admin
        if (!$user->hasRole('admin-level-1') && !$user->hasRole('admin-level-2')) {
            $query->where('user_id', $user->id);
        }

        return $query->paginate(10);
    }

    /**
     * Find a ticket by its ID with related messages and user.
     *
     * @param int $id The ticket ID.
     * @return Ticket The ticket model instance with 'messages.user' relationship loaded.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If ticket not found.
     */
    public function findById(int $id): Ticket
    {
        return Ticket::with('messages.user')->findOrFail($id);
    }

    /**
     * Create a new ticket record.
     *
     * @param array $data Associative array of ticket attributes (user_id, category_id, subject, priority, etc.).
     * @return Ticket The newly created ticket instance.
     */
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    /**
     * Retrieve paginated list of tickets for admin panel with advanced filtering.
     *
     * Supports filters: 'priority', 'status' (mapped to state classes), and 'search' (subject or user name).
     * If the user is only admin-level-1 (without level-2), automatically filters tickets with PendingLevel1 status.
     *
     * @param \App\Domains\User\Models\User $user The admin user performing the request.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Paginated tickets (10 per page).
     */
    public function paginateForAdmin($user)
    {
        $tickets = QueryBuilder::for(Ticket::class)
            ->allowedFilters([
                'priority',
                AllowedFilter::callback('status', function ($query, $value) {
                    // Map frontend simple status values to full state class names
                    $stateMap = [
                        'pending_level_1' => PendingLevel1::class,
                        'pending_level_2' => PendingLevel2::class,
                        'approved'       => Approved::class,
                        'closed'         => Closed::class,
                        'rejected'       => Rejected::class,
                        'failed'         => Failed::class,
                    ];

                    if (array_key_exists($value, $stateMap)) {
                        $query->where('status', $stateMap[$value]);
                    }
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    // Search within ticket subject and user name
                    $query->where(function ($q) use ($value) {
                        $q->where('subject', 'LIKE', "%{$value}%")
                            ->orWhereHas('user', function ($q2) use ($value) {
                                $q2->where('name', 'LIKE', "%{$value}%");
                            });
                    });
                }),
            ])
            ->allowedSorts(['created_at'])
            ->latest()
            ->with(['user']);

        // Level-1 admins (without level-2 role) can only see pending level 1 tickets
        if ($user->hasRole('admin-level-1') && !$user->hasRole('admin-level-2')) {
            $stateValue = PendingLevel1::class;
            $tickets->where('status', $stateValue);
        }

        return $tickets->paginate(10);
    }
}
