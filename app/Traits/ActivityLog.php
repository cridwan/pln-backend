<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;

trait ActivityLog
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<ActivityLog, $this>
     */
    public function activityLog(): MorphOne
    {
        return $this->morphOne(\App\Models\ActivityLog::class, 'activity');
    }
}
