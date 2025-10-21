<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;

trait ActivationStatus
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<ActivationStatus, $this>
     */
    public function status(): MorphOne
    {
        return $this->morphOne(\App\Models\ActivationStatus::class, 'status');
    }

    public function syncStatus($status)
    {
        return $this->status()->updateOrCreate([
            'status_id' => $this->id,
            'status_type' => static::class
        ], [
            'status' => boolval($status)
        ]);
    }
}
