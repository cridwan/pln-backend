<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'superuser@pln.com')->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Super User',
                'email' => 'superuser@pln.com',
                'password' => Hash::make('123@Password')
            ]);

            $user->syncRoles([
                RoleEnum::SUPERUSER
            ]);
        } else {
            $user->syncRoles([
                RoleEnum::SUPERUSER
            ]);
        }
    }
}
