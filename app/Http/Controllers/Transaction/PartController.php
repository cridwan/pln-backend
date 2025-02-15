<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Part;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transaction')]
class PartController extends Controller
{
    use HasPagination;

    protected $model = Part::class;
    protected array $search = [];
    protected array $with = [];
}
