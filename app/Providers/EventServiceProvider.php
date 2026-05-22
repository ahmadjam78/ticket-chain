<?php

namespace App\Providers;

use App\Domains\Ticket\Events\TicketStatusChanged;
use App\Domains\Ticket\Listeners\LogTicketStatusChange;
use App\Domains\Ticket\Listeners\SendTicketStatusNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * Maps events to their corresponding listeners.
 * Registers event listeners for ticket status changes.
 *
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TicketStatusChanged::class => [
            LogTicketStatusChange::class,
            SendTicketStatusNotification::class,
        ],
    ];
}
