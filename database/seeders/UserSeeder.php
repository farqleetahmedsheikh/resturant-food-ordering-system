<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'FreshBite Admin',
                'email' => 'admin@example.com',
                'phone' => '03000000001',
                'role' => 'admin',
            ],
            [
                'name' => 'Demo Customer',
                'email' => 'customer@example.com',
                'phone' => '03000000002',
                'role' => 'customer',
            ],
            [
                'name' => 'Second Customer',
                'email' => 'customer2@example.com',
                'phone' => '03000000022',
                'role' => 'customer',
            ],
            [
                'name' => 'Demo Rider One',
                'email' => 'rider@example.com',
                'phone' => '03000000003',
                'role' => 'rider',
            ],
            [
                'name' => 'Demo Rider Two',
                'email' => 'rider2@example.com',
                'phone' => '03000000004',
                'role' => 'rider',
            ],
            [
                'name' => 'Demo Rider Three',
                'email' => 'rider3@example.com',
                'phone' => '03000000005',
                'role' => 'rider',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'password' => 'password',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
