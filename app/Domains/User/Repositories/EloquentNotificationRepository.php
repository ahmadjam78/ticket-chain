<?php

namespace App\Domains\User\Repositories;

use Illuminate\Notifications\Notification;

class EloquentNotificationRepository implements NotificationRepository
{

    public function paginate($user)
    {
        $notifications =$user
            ->notifications()
            ->paginate(15);

        return $notifications;
    }

    public function unread($user)
    {
        $notifications = $user
            ->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $notifications;
    }

    public function findById(string $id, $user)
    {
        $notification = $user
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        return $notification;
    }
}
