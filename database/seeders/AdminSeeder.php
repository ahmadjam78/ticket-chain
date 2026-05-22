<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
use App\Shared\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminLevel1 = User::firstOrCreate(
            ['email' => 'admin-level1@example.com'],
            [
                'name'     => 'Admin Level 1',
                'password' => Hash::make('password'),
            ]
        );
        $adminLevel1->assignRole(Role::ADMIN_LEVEL_1->value);

        $adminLevel2 = User::firstOrCreate(
            ['email' => 'admin-level2@example.com'],
            [
                'name'     => 'Admin Level 2',
                'password' => Hash::make('password'),
            ]
        );
        $adminLevel2->assignRole(Role::ADMIN_LEVEL_2->value);
    }
}
