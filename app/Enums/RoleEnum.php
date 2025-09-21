<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPERUSER = 'superuser';
    case PLANNER = 'planner';
    case APPROVAL = 'approval';

    public static function transactionRole()
    {
        return [
            self::PLANNER,
            self::APPROVAL
        ];
    }
}
