<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', RoleEnum::SUPERUSER->value)->first();

        if (!$role) {
            Role::create([
                'name' => RoleEnum::SUPERUSER->value,
                'display_name' => 'Super User',
                'guard_name' => 'api',
                'uuid' => Str::uuid()
            ]);
        }
    }
}
