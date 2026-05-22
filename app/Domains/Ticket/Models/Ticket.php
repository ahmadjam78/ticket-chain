<?php

namespace App\Domains\Ticket\Models;

use App\Domains\Ticket\States\TicketState;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Shared\Enums\TicketStatus;
use App\Shared\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStates\HasStates;

/**
 * Class Ticket
 *
 * Represents a support ticket within the system. Handles ticket lifecycle,
 * user association, category, messages, activity logs, and status transitions.
 *
 * @package App\Domains\Ticket\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $subject
 * @property \App\Domains\Ticket\States\TicketState $status
 * @property \App\Shared\Enums\TicketPriority $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domains\User\Models\User $user
 * @property-read \App\Domains\Ticket\Models\TicketCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domains\Ticket\Models\TicketMessage[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domains\Ticket\Models\TicketWebServiceLog[] $logs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domains\Ticket\Models\TicketStatusLog[] $statusLogs
 * @property-read string $status_color
 */
class Ticket extends Model
{
    use HasStates;
    use LogsActivity;

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var string[]
     */
    protected $appends = ['status_color'];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'subject',
        'status',
        'priority',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status'   => TicketState::class,
        'priority' => TicketPriority::class,
    ];

    // ------------------------------ Relations ------------------------------

    /**
     * Get the user who created the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Domains\User\Models\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category this ticket belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Domains\Ticket\Models\TicketCategory
     */
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * Get all messages associated with this ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Domains\Ticket\Models\TicketMessage
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    /**
     * Get all web service logs for this ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Domains\Ticket\Models\TicketWebServiceLog
     */
    public function logs()
    {
        return $this->hasMany(TicketWebServiceLog::class);
    }

    /**
     * Get all status change logs for this ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Domains\Ticket\Models\TicketStatusLog
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(TicketStatusLog::class);
    }

    // ------------------------------ Helpers ------------------------------

    /**
     * Determine if the ticket is currently open (pending level 1).
     *
     * @return bool True if ticket status is PENDING_LEVEL_1, false otherwise.
     */
    public function isOpen(): bool
    {
        return $this->status === TicketStatus::PENDING_LEVEL_1;
    }

    /**
     * Determine if the ticket is closed.
     *
     * @return bool True if ticket status is CLOSED, false otherwise.
     */
    public function isClosed(): bool
    {
        return $this->status === TicketStatus::CLOSED;
    }

    /**
     * Configure activity logging for this model.
     *
     * Logs all attributes, only dirty changes, and ignores empty logs.
     *
     * @return \Spatie\Activitylog\LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the color representation of the current ticket status.
     *
     * Delegates to the status instance's `color()` method if available,
     * otherwise returns a default gray color.
     *
     * @return string Hex color code or named color (e.g., 'gray').
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }
}
