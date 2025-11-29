<?php

namespace App\Observers;

class UserSafeObserver
{
    public function created($model)
    {
        if (method_exists($model, 'activityLog')) {
            $model->activityLog()->updateOrCreate([
                'activity_id' => $model->id,
                'activity_type' => $model::class
            ], [
                'created_id' => auth()->user()?->id,
            ]);
        }
    }

    public function updated($model)
    {
        if (method_exists($model, 'activityLog')) {
            $model->activityLog()->updateOrCreate([
                'activity_id' => $model->id,
                'activity_type' => $model::class
            ], [
                'updated_id' => auth()->user()?->id,
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
