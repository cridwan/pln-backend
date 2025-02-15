<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\QcPlan;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transaction')]
class QcPlanController extends Controller
{
    use HasPagination;

    protected $model = QcPlan::class;
    protected array $search = [];
    protected array $with = [];
}
