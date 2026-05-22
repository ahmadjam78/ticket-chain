<?php

namespace App\Providers;

use App\Domains\Ticket\Repositories\EloquentTicketCategoryRepository;
use App\Domains\Ticket\Repositories\EloquentTicketMessageRepository;
use App\Domains\Ticket\Repositories\EloquentWebServiceLogRepository;
use App\Domains\Ticket\Repositories\TicketCategoryRepository;
use App\Domains\Ticket\Repositories\TicketMessageRepository;
use App\Domains\Ticket\Repositories\WebServiceLogRepository;
use App\Domains\Ticket\Resources\V1\Admin\WebServiceLogResource;
use App\Domains\User\Repositories\EloquentNotificationRepository;
use App\Domains\User\Repositories\EloquentUserRepository;
use App\Domains\User\Repositories\NotificationRepository;
use App\Domains\User\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Domains\Ticket\Repositories\TicketRepository;
use App\Domains\Ticket\Repositories\EloquentTicketRepository;

/**
 * Class RepositoryServiceProvider
 *
 * Registers repository interface bindings with their concrete implementations.
 * This allows dependency injection of interfaces throughout the application,
 * promoting loose coupling and easier testing.
 *
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any repository bindings for the application.
     *
     * Binds each repository interface to its corresponding Eloquent implementation.
     * These bindings enable Laravel's service container to resolve interfaces
     * automatically when type-hinted in constructors or methods.
     *
     * @return void
     */
    public function register()
    {
        // Ticket repository binding
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);

        // Ticket message repository binding
        $this->app->bind(TicketMessageRepository::class, EloquentTicketMessageRepository::class);

        // User repository binding
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);

        // Ticket category repository binding
        $this->app->bind(TicketCategoryRepository::class, EloquentTicketCategoryRepository::class);

        // Web service log repository binding
        $this->app->bind(WebServiceLogRepository::class, EloquentWebServiceLogRepository::class);

        // Notification repository binding
        $this->app->bind(NotificationRepository::class, EloquentNotificationRepository::class);
    }
}
