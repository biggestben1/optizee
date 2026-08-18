<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Full system access. Can create and manage staff, customers, and suppliers. Set prices, credit limits, and payment terms. View all reports.',
            ],
            [
                'name' => 'supervisor',
                'display_name' => 'Supervisor',
                'description' => 'Monitor daily sales and staff activities. Open and close shifts. Approve voids and monitor credit usage.',
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'Sell drinks and food. Accept cash, transfer, POS, or credit sales. Receive customer credit payments.',
            ],
            [
                'name' => 'storekeeper',
                'display_name' => 'Store Keeper',
                'description' => 'Add and manage stock. Record stock received from suppliers. Track damaged or expired items.',
            ],
            [
                'name' => 'kitchen',
                'display_name' => 'Kitchen Staff',
                'description' => 'View orders from cashiers. Mark items as ready when prepared. Print kitchen orders.',
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant',
                'description' => 'Access to all accounting records, financial reports, journals, and asset management.',
            ],
            [
                'name' => 'director',
                'display_name' => 'Director',
                'description' => 'Director (administrator-equivalent). Full system access similar to administrators.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}


