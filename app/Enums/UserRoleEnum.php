<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case OWNER = 'owner';
    case EMPLOYEE = 'employee';

    public static function labels(): array
    {
        return [
            self::OWNER->value => 'Owner',
            self::EMPLOYEE->value => 'Employee',
        ];
    }
}
