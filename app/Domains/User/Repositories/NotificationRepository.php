<?php

namespace App\Domains\User\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepository
{
    public function paginate($user);
    public function unread($user);
    public function findById(string $id, $user);
}
