<?php

namespace App\Domains\Ticket\Repositories;

use App\Domains\Ticket\Models\TicketWebServiceLog;

interface WebServiceLogRepository
{
    public function paginateForAdmin();
    public function create(array $data): TicketWebServiceLog;
    public function attemptCount(int $id): int;
}
