<?php

namespace App\Enums;

enum DatabaseConnectionEnum: string
{
    case GLOBAL = 'tensor_global';
    case TRANSACTION = 'tensor_transaction';
    case DOCUMENT = 'tensor_document';
}
