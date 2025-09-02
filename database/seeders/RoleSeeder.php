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
        collect([
            [
                "name" => RoleEnum::SUPERUSER->value,
                "display_name" => "Super User"
            ],
            [
                "name" => RoleEnum::PLANNER->value,
                "display_name" => "Planner"
            ],
            [
                "name" => RoleEnum::APPROVAL->value,
                "display_name" => "Approval"
            ]
        ])->each(function ($item) {
            Role::firstOrCreate([
                'name' => $item['name'],
            ], [
                'display_name' => $item['display_name'],
                'guard_name' => 'api',
                'uuid' => Str::uuid()
            ]);
        });
    }
}
