<?php

namespace Database\Seeders;

use App\Shared\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Domains\User\Models\User;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name'     => 'Test Customer',
                'password' => Hash::make('password'),
            ]
        );
        $customer->assignRole(Role::CUSTOMER->value);
    }
}
