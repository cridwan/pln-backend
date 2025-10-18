<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class Location extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_uuid', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_uuid', 'id');
    }
}
