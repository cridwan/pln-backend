<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ALERT = 'alert';
    case REQUEST = 'request';
}
