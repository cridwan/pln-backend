<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Manpower;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transaction')]
class ManpowerController extends Controller
{
    use HasPagination;

    protected $model = Manpower::class;
    protected array $search = [];
    protected array $with = [];
}
