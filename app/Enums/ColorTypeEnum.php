<?php

namespace App\Enums;

enum ColorTypeEnum: string
{
    case RED = "red";
    case GREEN = "green";
    case YELLOW = "yellow";
    case CLEAR = "";


    public function color()
    {
        return match ($this) {
            self::RED => 'DC143C',
            self::GREEN => '78C841',
            self::YELLOW => 'FFCC00',
            default => 'FFFFFF'
        };
    }
}
