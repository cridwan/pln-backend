<?php

namespace App\Observers;

use App\Models\Notification;
use App\Notifications\RequestApproveNotification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class NotificationSafeObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Notification $notification)
    {
        $notification->loadMissing(['receivedBy']);

        if ($notification->receivedBy) {
            $notification->receivedBy->notify(new RequestApproveNotification($notification));
        }
    }
}
