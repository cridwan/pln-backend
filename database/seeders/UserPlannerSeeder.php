<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserPlannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'planner@pln.com')->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Planner',
                'email' => 'planner@pln.com',
                'password' => Hash::make('123@Password')
            ]);

            $user->syncRoles([
                RoleEnum::PLANNER
            ]);
        } else {
            $user->syncRoles([
                RoleEnum::PLANNER
            ]);
        }
    }
}
