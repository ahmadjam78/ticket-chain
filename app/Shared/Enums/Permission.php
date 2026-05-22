<?php

namespace App\Shared\Enums;

/**
 * Enum Permission
 *
 * Defines available permission keys for role-based access control (RBAC).
 * Used to assign granular permissions to roles and check user authorizations.
 *
 * @package App\Shared\Enums
 */
enum Permission: string
{
    // Basic permissions for regular users
    case CREATE_TICKETS = 'create tickets';
    case VIEW_TICKETS   = 'view tickets';

    // Administrative permissions for ticket approval workflow
    case APPROVE_LEVEL_1 = 'approve level 1';
    case APPROVE_LEVEL_2 = 'approve level 2';
    case REJECT_TICKET   = 'reject ticket';

    /**
     * Get all permission values as an array.
     *
     * @return array<string> List of all permission string values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
