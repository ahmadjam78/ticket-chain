<?php

namespace App\Domains\User\Models;

use App\Domains\Ticket\Models\Ticket;
use App\Domains\Ticket\Models\TicketMessage;
use App\Shared\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * Represents an application user with role-based permissions using Spatie Permission.
 * Supports admin and customer roles, and relationships with tickets and messages.
 *
 * @package App\Domains\User\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domains\Ticket\Models\Ticket[] $tickets
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domains\Ticket\Models\TicketMessage[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read string|null $role
 */
class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, HasFactory;

    /**
     * The guard name used for authentication and authorization.
     *
     * @var string
     */
    protected $guard_name = 'web';

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var string[]
     */
    protected $appends = ['role'];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    // ------------------------------ Relations ------------------------------

    /**
     * Get all tickets created by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get all ticket messages authored by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    // ------------------------------ Accessors ------------------------------

    /**
     * Get the user's primary role as a string.
     *
     * @return string|null The role name, or null if no role assigned.
     */
    public function getRoleAttribute(): ?string
    {
        return $this->roles->pluck('name')->first();
    }

    // ------------------------------ Helpers ------------------------------

    /**
     * Determine if the user has admin privileges (level 1 or level 2).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN_LEVEL_1->value) || $this->hasRole(Role::ADMIN_LEVEL_2->value);
    }

    /**
     * Determine if the user is a customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
    {
        return $this->hasRole(Role::CUSTOMER->value);
    }
}
