<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Policies\TicketPolicy;

/**
 * Class AuthServiceProvider
 *
 * Registers application authorization policies.
 * Maps Ticket model to its corresponding policy for authorization checks.
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Ticket::class => TicketPolicy::class,
    ];

    /**
     * Bootstrap any application authentication / authorization services.
     *
     * Registers the defined policies with Laravel's authorization system.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
