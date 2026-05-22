<?php

namespace App\Shared\Enums;

/**
 * Enum TicketStatus
 *
 * Defines all possible statuses for a support ticket throughout its lifecycle.
 * Includes pending approval levels, approved, rejected, closed, failed, and processing.
 *
 * @package App\Shared\Enums
 */
enum TicketStatus: string
{
    case PENDING_LEVEL_1 = 'pending_level_1';   // Awaiting level 1 admin approval
    case PENDING_LEVEL_2 = 'pending_level_2';   // Awaiting level 2 admin approval
    case APPROVED        = 'approved';          // Final approval (web service responded successfully)
    case REJECTED        = 'rejected';          // Rejected by admin at any stage
    case CLOSED          = 'closed';            // Closed by user or admin after completion
    case FAILED          = 'failed';            // Failed to send to web service after multiple attempts
    case PROCESSING      = 'processing';        // Processing

    /**
     * Get the human-readable label for the status.
     *
     * @return string The label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING_LEVEL_1 => 'Pending Level 1',
            self::PENDING_LEVEL_2 => 'Pending Level 2',
            self::APPROVED        => 'Approved',
            self::REJECTED        => 'Rejected',
            self::CLOSED          => 'Closed',
            self::FAILED          => 'Failed',
            self::PROCESSING      => 'Processing',
        };
    }

    /**
     * Determine if the ticket is in a final (closeable) state.
     *
     * Final states include approved, rejected, failed, and closed.
     *
     * @return bool True if the status is final, false otherwise.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::FAILED, self::CLOSED]);
    }

    /**
     * Get all ticket status values as an array.
     *
     * @return array<string> List of all status string values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
