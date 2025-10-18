<?php

namespace App\Observers;

class UppercaseObservser
{

    private static $exceptAttributes = [
        'uuid',
        '_uuid',
        'created_at',
        'updated_at',
        'deleted_at',
        'password',
        'description'
    ];

    private function toUpperCase($model)
    {
        foreach ($model->getAttributes() as $key => $value) {
            if (
                is_string($value) &&
                !is_null($value) &&
                !in_array($key, self::$exceptAttributes, true) &&
                !str_ends_with($key, '_uuid')
            ) {
                $model->{$key} = strtoupper($value);
            }
        }
    }

    public function creating($model)
    {
        $this->toUpperCase($model);
        // user activity observer
        (new UserActivity())->creating($model);
    }

    public function updating($model)
    {
        $this->toUpperCase($model);
        // user activity observer
        (new UserActivity())->updating($model);
    }
}
