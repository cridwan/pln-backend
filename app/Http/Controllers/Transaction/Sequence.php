<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Sequence as TransactionSequence;
use App\Traits\HasPagination;

class Sequence extends Controller
{
    use HasPagination;

    protected $model = TransactionSequence::class;
    protected array $search = [];
    protected array $with = [];
}
