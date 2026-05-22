<?php

namespace App\Domains\Ticket\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TicketWebServiceLog
 *
 * Logs web service requests and responses related to a ticket.
 * Tracks the status (success/failed), attempt number, and response payload.
 *
 * @package App\Domains\Ticket\Models
 *
 * @property int $id
 * @property int $ticket_id
 * @property string $status
 * @property int $attempt_number
 * @property string|null $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domains\Ticket\Models\Ticket $ticket
 */
class TicketWebServiceLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_web_service_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'status',
        'attempt_number',
        'response',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attempt_number' => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // ------------------------------ Relations ------------------------------

    /**
     * Get the ticket that this web service log belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // ------------------------------ Scopes ------------------------------

    /**
     * Scope a query to only include successful web service logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed web service logs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    // ------------------------------ Helpers ------------------------------

    /**
     * Determine if the web service log entry represents a successful request.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Determine if the web service log entry represents a failed request.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
