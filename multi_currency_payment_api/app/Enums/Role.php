<?php

namespace App\Enums;

enum Role: string
{
    case FINANCE_ADMIN = 'finance_admin';
    case EMPLOYEE = 'employee';

    public static function values(): array
    {
        return array_map(fn(self $role) => $role->value, self::cases());
    }
}
