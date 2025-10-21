<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPERUSER = 'superuser';
    case PLANNER = 'planner';
    case APPROVAL = 'approval';
    case GUEST = 'guest';

    public static function transactionRole()
    {
        return [
            self::PLANNER,
            self::APPROVAL
        ];
    }

    public static function masterRole()
    {
        return [
            self::SUPERUSER
        ];
    }

    public static function guestRole()
    {
        return [
            self::GUEST,
        ];
    }

    public static function accessProject()
    {
        return [
            self::APPROVAL,
            self::GUEST,
            self::PLANNER
        ];
    }
}
