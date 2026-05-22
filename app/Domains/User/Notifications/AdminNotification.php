<?php

namespace App\Domains\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Class AdminNotification
 *
 * Notification class for sending admin-style alerts to users.
 * Stores notifications in the database and can be extended for other channels.
 *
 * @package App\Domains\User\Notifications
 */
class AdminNotification extends Notification
{
    use Queueable;

    /**
     * The notification title.
     *
     * @var string
     */
    protected $title;

    /**
     * The notification message body.
     *
     * @var string
     */
    protected $message;

    /**
     * The type/level of the notification (e.g., info, success, warning, danger).
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new notification instance.
     *
     * @param string $title   The notification title.
     * @param string $message The notification message content.
     * @param string $type    The notification type (default: 'info').
     */
    public function __construct(string $title, string $message, string $type = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable The notifiable entity (usually a User model).
     * @return array<string> Array of channel names.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable The notifiable entity.
     * @return array<string, mixed> The notification data.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title'     => $this->title,
            'message'   => $this->message,
            'type'      => $this->type,
            'timestamp' => now(),
        ];
    }

    /**
     * Get the array representation of the notification for UI consumption.
     *
     * @param mixed $notifiable The notifiable entity.
     * @return array<string, mixed> The notification data.
     */
    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
