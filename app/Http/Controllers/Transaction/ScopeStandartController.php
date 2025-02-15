<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ScopeStandart;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transaction')]
class ScopeStandartController extends Controller
{
    use HasPagination;

    protected $model = ScopeStandart::class;
    protected array $search = [];
    protected array $with = [];
}
