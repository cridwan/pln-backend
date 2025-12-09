<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function deleting(User $user)
    {
        $exists = $user->activityLog()->exists() || $user->projectActivities()->exists();

        if ($exists) {
            throw new \Exception("Data {$user->name} sudah digunakan di data lain");
        }
    }
}
