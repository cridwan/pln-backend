<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Transaction\ProjectActivity;
use App\Observers\UserObserver;
use App\Observers\UserSafeObserver;
use App\Traits\ActivityLog;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[ObservedBy([UserSafeObserver::class, UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, ActivityLog;

    protected $connection = 'masterdata';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_create',
        'area_uuid'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_create' => 'boolean'
        ];
    }

    public function guardName()
    {
        return 'api';
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_uuid');
    }

    public function matchRoles($roles, $guard = 'api')
    {
        $roles = is_array($roles) ? $roles : explode('|', $roles);
        \Log::info('role', [
            'data' => $roles,
            'query' => $this->roles()
                ->get()
        ]);
        return $this->roles()
            ->whereIn('name', $roles)
            ->where('guard_name', $guard)
            ->exists();
    }

    public function projectActivities()
    {
        return $this->hasMany(ProjectActivity::class, 'user_id');
    }
}
