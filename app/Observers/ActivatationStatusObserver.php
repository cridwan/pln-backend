<?php

namespace App\Observers;

class ActivatationStatusObserver
{
    public function created($model)
    {
    }

    public function updated($model)
    {
    }

    public function deleted($model)
    {
        if (method_exists($this, 'status')) {
            $this->status()->delete();
        }
    }
}
