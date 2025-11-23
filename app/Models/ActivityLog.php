<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasUuids;

    public function getKeyName(): string
    {
        return 'uuid';
    }

    public function getGuarded()
    {
        return ['created_at', 'updated_at']; // Kembalikan nilai guarded sesuai kebutuhan
    }

    public function getConnectionString()
    {
        return $this->connection;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_id', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_id', 'id');
    }
}
