<?php

namespace App\Models;

use App\Observers\RoleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Spatie\Permission\Models\Role as ModelsRole;

#[ObservedBy(RoleObserver::class)]
class Role extends ModelsRole {}
