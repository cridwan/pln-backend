<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', RoleEnum::PLANNER->value)->first();

        if (!$role) {
            Role::create([
                'name' => RoleEnum::PLANNER->value,
                'display_name' => 'Planner',
                'guard_name' => 'api',
                'uuid' => Str::uuid()
            ]);
        }
    }
}
