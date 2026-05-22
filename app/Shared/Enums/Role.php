<?php

namespace App\Shared\Enums;

/**
 * Enum Role
 *
 * Defines the available user roles within the application.
 * Used for role-based authorization and permission management.
 *
 * @package App\Shared\Enums
 */
enum Role: string
{
    case CUSTOMER      = 'customer';
    case ADMIN_LEVEL_1 = 'admin-level-1';
    case ADMIN_LEVEL_2 = 'admin-level-2';

    /**
     * Get all role values as an array.
     *
     * @return array<string> List of all role string values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
