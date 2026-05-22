<?php

namespace App\Domains\Ticket\Repositories;

use App\Domains\Ticket\Models\TicketWebServiceLog;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class EloquentWebServiceLogRepository
 *
 * Eloquent implementation of the WebServiceLogRepository interface.
 * Handles database operations for ticket web service logs including pagination,
 * filtering, sorting, creation, and attempt counting.
 *
 * @package App\Domains\Ticket\Repositories
 */
class EloquentWebServiceLogRepository implements WebServiceLogRepository
{
    /**
     * Retrieve paginated list of web service logs for admin panel.
     *
     * Supports filtering by 'status' field and sorting by 'created_at'.
     * Eager loads the associated ticket and orders by latest first.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Paginated logs (15 per page).
     */
    public function paginateForAdmin()
    {
        $logs = QueryBuilder::for(TicketWebServiceLog::class)
            ->allowedFilters([
                'status',
            ])
            ->allowedSorts(['created_at'])
            ->latest()
            ->with(['ticket']);

        return $logs->paginate(15);
    }

    /**
     * Create a new web service log record.
     *
     * @param array $data Associative array of log attributes (ticket_id, status, attempt_number, response).
     * @return TicketWebServiceLog The newly created log model instance.
     */
    public function create(array $data): TicketWebServiceLog
    {
        return TicketWebServiceLog::create($data);
    }

    /**
     * Get the total number of web service attempts for a given ticket.
     *
     * @param int $id The ticket ID.
     * @return int The count of log entries associated with the ticket.
     */
    public function attemptCount(int $id): int
    {
        return TicketWebServiceLog::where('ticket_id', $id)->count();
    }
}
