<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ActivationStatus extends Model
{
    use HasUuids;

    protected $casts = [
        'status' => 'boolean'
    ];

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
}
