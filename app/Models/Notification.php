<?php

namespace App\Models;

use App\Enums\NotificationTypeEnum;
use App\Observers\NotificationSafeObserver;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([NotificationSafeObserver::class])]
class Notification extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    protected $casts = [
        'is_read' => 'boolean',
        'type' => NotificationTypeEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function sendBy()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
