<?php

namespace App\Domains\Ticket\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TicketStatusLog
 *
 * Logs status change history for tickets.
 * Records the previous status, new status, and the user who performed the change.
 *
 * @package App\Domains\Ticket\Models
 *
 * @property int $id
 * @property int $ticket_id
 * @property string $old_status
 * @property string $new_status
 * @property int $changed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domains\Ticket\Models\Ticket $ticket
 * @property-read \App\Domains\User\Models\User $changedBy
 * @property-read string $old_status_label
 * @property-read string $new_status_label
 */
class TicketStatusLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_status_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'old_status',
        'new_status',
        'changed_by',
    ];

    // ------------------------------ Relations ------------------------------

    /**
     * Get the ticket that owns this status log entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Get the user who changed the ticket status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ------------------------------ Accessors ------------------------------

    /**
     * Get a human-readable label for the old status.
     *
     * Converts internal status strings (with underscores or backslashes)
     * into a properly capitalized, readable format.
     *
     * @return string
     */
    public function getOldStatusLabelAttribute(): string
    {
        return $this->formatStatusLabel($this->old_status);
    }

    /**
     * Get a human-readable label for the new status.
     *
     * Converts internal status strings (with underscores or backslashes)
     * into a properly capitalized, readable format.
     *
     * @return string
     */
    public function getNewStatusLabelAttribute(): string
    {
        return $this->formatStatusLabel($this->new_status);
    }

    // ------------------------------ Helpers ------------------------------

    /**
     * Format a status string into a human-readable label.
     *
     * Replaces underscores and backslashes with spaces, trims whitespace,
     * and capitalizes the first letter of each word.
     *
     * @param string $status The raw status string.
     * @return string The formatted status label.
     */
    protected function formatStatusLabel(string $status): string
    {
        // Replace underscores/slashes with spaces and capitalize
        $cleaned = str_replace(['_', '\\'], ' ', $status);
        return ucwords(trim($cleaned));
    }
}
