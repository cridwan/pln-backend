<?php

namespace App\Observers;

class ActivityLogObserver
{
    public function created($model)
    {
        if (method_exists($model, 'activityLog')) {
            $model->activityLog()->updateOrCreate([
                'activity_id' => $model->uuid,
                'activity_type' => $model::class
            ], [
                'created_id' => auth()->user()->id,
            ]);
        }
    }

    public function updated($model)
    {
        if (method_exists($model, 'activityLog')) {
            $model->activityLog()->updateOrCreate([
                'activity_id' => $model->uuid,
                'activity_type' => $model::class
            ], [
                'updated_id' => auth()->user()->id,
            ]);
        }
    }

    public function deleted($model)
    {
        if (method_exists($model, 'activityLog')) {
            $model->activityLog()->delete();
        }
    }
}
