<?php

namespace App\Observers;

class UserActivity
{
    public function creating($model)
    {
        if (auth()->user() && array_key_exists('created_uuid', $model->getAttributes())) {
            $model->created_uuid = auth()->user()->uuid;
        }
    }

    public function updating($model)
    {
        if (auth()->user() && array_key_exists('updated_uuid', $model->getAttributes())) {
            $model->updated_uuid = auth()->user()->id;
        }
    }
}
