<?php

namespace App\Enums;

enum ProjectStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVE = 'approve';

    public function updateStatus(): ProjectStatusEnum
    {
        return $this == self::PENDING ? self::APPROVE : self::PENDING;
    }
}
