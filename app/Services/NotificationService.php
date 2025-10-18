<?php

namespace App\Services;

use App\Data\NotificationData;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationService
{
    public function store(NotificationData $data)
    {
        return Notification::create($data->toArray());
    }

    public function show(Notification $notification)
    {
        return $notification;
    }

    public function markAsRead(Notification $notification)
    {
        $notification->is_read = true;
        $notification->save();

        return $notification;
    }

    public function destroy(Notification $notification)
    {
        return $notification->delete();
    }

    public function update(Notification $notification, NotificationData $data)
    {
        $notification->update($data->toArray());

        return $notification;
    }

    public function pagination($perPage = 15)
    {
        return Notification::paginate($perPage);
    }

    public function latest(Request $request)
    {
        return Notification::latest()
            ->where('is_read', '=', false)
            ->when($request->filled('receiver_id'), fn($query) => $query->where('receiver_id', '=', $request->receiver_id))
            ->get();
    }
}
