<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@admin.com');
        $this->command->info('Password: admin');
    }
}

