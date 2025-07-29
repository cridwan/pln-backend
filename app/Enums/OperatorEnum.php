<?php

namespace App\Enums;

enum OperatorEnum: string
{
    case EQ = 'EQ';
    case LIKE = 'LIKE';
    case NEQ = 'NEQ';
    case GT = 'GT';
    case GTE = 'GTE';
    case LT = 'LT';
    case LTE = 'LTE';
    case IN = 'IN';
    case IS_NULL = 'IS_NULL';
    case NOT_NULL = 'NOT_NULL';
}
