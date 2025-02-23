<?php

namespace App\Http\Controllers\Transaction\Hse;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Hse;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transaction Hse Resource')]
class ResourceController extends Controller
{
    use HasPagination;

    protected $model = Hse::class;
    protected array $search = [];
    protected array $with = [];
}
