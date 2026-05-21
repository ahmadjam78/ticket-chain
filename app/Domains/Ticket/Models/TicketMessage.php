<?php

namespace App\Domains\Ticket\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class TicketMessage
 *
 * Represents a message or reply within a support ticket.
 * Messages can have attachments (images, PDFs, zip files) via Spatie Media Library.
 *
 * @package App\Domains\Ticket\Models
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domains\Ticket\Models\Ticket $ticket
 * @property-read \App\Domains\User\Models\User $user
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 */
class TicketMessage extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
    ];

    // ------------------------------ Relations ------------------------------

    /**
     * Get the ticket that this message belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user who authored this message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ------------------------------ Media Library ------------------------------

    /**
     * Register media collections for attachments.
     *
     * Defines an 'attachments' collection that stores files on the 'public' disk.
     * Only allows specific MIME types: JPEG, PNG, PDF, and ZIP.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('public')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/zip'
            ]);
    }
}
