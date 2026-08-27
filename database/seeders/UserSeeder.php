<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admins
        User::updateOrCreate(
            ['email' => 'admin@blue.com'],
            [
                'name' => 'Chun Visal',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'employee_id' => 'EMP-001',
                'phone' => '0888093342',
                'address' => '123 Main St, Phnom Penh',
                'shift' => 'full-morning',
                'hire_date' => '2024-01-15',
                'salary' => 2500.00,
            ]
        );

        User::updateOrCreate(
            ['email' => 'ann.yon@blue.com'],
            [
                'name' => 'Ann Yon',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'employee_id' => 'EMP-002',
                'phone' => '012 999 888',
                'address' => '456 Central Ave, Phnom Penh',
                'shift' => 'full-night',
                'hire_date' => '2024-03-01',
                'salary' => 2200.00,
            ]
        );

        // Cashiers with full info
        $cashiers = [
            [
                'name' => 'Makara Khero',
                'email' => 'makara.khero@blue.com',
                'employee_id' => 'EMP-101',
                'phone' => '015 111 222',
                'address' => '12 Street 51, Phnom Penh',
                'shift' => 'morning-afternoon',
                'pin' => '0000',
                'hire_date' => '2024-06-01',
                'salary' => 800.00,
            ],
            [
                'name' => 'Sor Ehour',
                'email' => 'sor.ehour@blue.com',
                'employee_id' => 'EMP-102',
                'phone' => '016 333 444',
                'address' => '34 Street 271, Phnom Penh',
                'shift' => 'afternoon-night',
                'pin' => '0000',
                'hire_date' => '2024-06-15',
                'salary' => 750.00,
            ],
            [
                'name' => 'Soun Mony',
                'email' => 'soun.mony@blue.com',
                'employee_id' => 'EMP-103',
                'phone' => '017 555 666',
                'address' => '56 Street 2004, Phnom Penh',
                'shift' => 'full-morning',
                'pin' => '0000',
                'hire_date' => '2024-07-01',
                'salary' => 900.00,
            ],
            [
                'name' => 'Makara Pichmony',
                'email' => 'makara.pichmony@blue.com',
                'employee_id' => 'EMP-104',
                'phone' => '018 777 888',
                'address' => '78 Russian Blvd, Phnom Penh',
                'shift' => 'night-morning',
                'pin' => '0000',
                'hire_date' => '2024-07-15',
                'salary' => 850.00,
            ],
            [
                'name' => 'Soeun Sophalin',
                'email' => 'soeun.sophalin@blue.com',
                'employee_id' => 'EMP-105',
                'phone' => '019 999 000',
                'address' => '90 Monivong Blvd, Phnom Penh',
                'shift' => 'full-night',
                'pin' => '0000',
                'hire_date' => '2024-08-01',
                'salary' => 950.00,
            ],
            [
                'name' => 'Hon Lyheng',
                'email' => 'hon.lyheng@blue.com',
                'employee_id' => 'EMP-106',
                'phone' => '088 111 333',
                'address' => '23 Norodom Blvd, Phnom Penh',
                'shift' => 'morning-afternoon',
                'pin' => '0000',
                'hire_date' => '2024-08-15',
                'salary' => 780.00,
            ],
            [
                'name' => 'Sor Lyna',
                'email' => 'sor.lyna@blue.com',
                'employee_id' => 'EMP-107',
                'phone' => '077 444 555',
                'address' => '45 Mao Tse Toung, Phnom Penh',
                'shift' => 'afternoon-night',
                'pin' => '0000',
                'hire_date' => '2024-09-01',
                'salary' => 820.00,
            ],
            [
                'name' => 'Da Nita',
                'email' => 'da.nita@blue.com',
                'employee_id' => 'EMP-108',
                'phone' => '066 777 999',
                'address' => '67 Sihanouk Blvd, Phnom Penh',
                'shift' => 'full-morning',
                'pin' => '0000',
                'hire_date' => '2024-09-15',
                'salary' => 880.00,
            ],
            [
                'name' => '@Cashier',
                'email' => 'cashier@blue.com',
                'employee_id' => 'EMP-100',
                'phone' => '011 222 333',
                'address' => '1 Default St, Phnom Penh',
                'shift' => 'morning-afternoon',
                'pin' => '0000',
                'hire_date' => '2024-05-01',
                'salary' => 700.00,
            ],
        ];

        foreach ($cashiers as $cashier) {
            User::updateOrCreate(
                ['email' => $cashier['email']],
                [
                    'name' => $cashier['name'],
                    'password' => Hash::make('cashier123'),
                    'role' => 'cashier',
                    'status' => 'active',
                    'employee_id' => $cashier['employee_id'],
                    'phone' => $cashier['phone'],
                    'address' => $cashier['address'],
                    'shift' => $cashier['shift'],
                    'pin' => $cashier['pin'],
                    'hire_date' => $cashier['hire_date'],
                    'salary' => $cashier['salary'],
                ]
            );
        }
    }
}
