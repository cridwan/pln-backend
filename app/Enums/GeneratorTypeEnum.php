<?php

namespace App\Enums;
enum GeneratorTypeEnum: string
{
    case PLTG_U = 'D84040';
    case PLTU = '000000';
    case PLTA = 'EB5A3C';
    case PLTP = '7E5CAD';
    case PLTD_G = 'ECE852';
    case PLTMG = 'FFFFFF';

    public static function getType(string $color): ?self
    {
        foreach (self::cases() as $case) {
            if (str($color)->upper()->contains($case->value)) {
                return $case;
            }
        }

        return null; // Jika tidak ditemukan
    }
}
