<?php


namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait SettingModel
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
}
