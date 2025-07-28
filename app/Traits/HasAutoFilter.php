<?php

namespace App\Traits;

use App\Models\Scopes\FilteredScope;
use App\Scopes\FilterScope;

trait HasAutoFilter
{
    public static function bootHasAutoFilter(): void
    {
        static::addGlobalScope(new FilteredScope());
    }
}
