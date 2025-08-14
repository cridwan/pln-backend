<?php

namespace App\Enums;

enum ConnectionEnum: string
{
    case GLOBAL = 'mysql';
    case TRANSACTION = 'transaction';
    case DOCUMENT = 'document';
}
