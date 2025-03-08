<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = array_map(fn($case) => $case->value, PermissionEnum::cases());


        foreach ($permissions as $permission) {
            $exist = Permission::where('name', $permission)->first();

            if ($exist) {
                $exist->update([
                    'uuid' => Str::uuid(),
                    'guard_name' => 'api',
                    'display_name' => ucfirst(implode(' ', explode('-', $permission)))
                ]);
            } else {
                Permission::create([
                    'uuid' => Str::uuid(),
                    'name' => $permission,
                    'guard_name' => 'api',
                    'display_name' => ucfirst(implode(' ', explode('-', $permission)))
                ]);
            }
        }
    }
}
