<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['administrator', 'Administrator', 'admin@example.com', 'admin', 'Admin12345'],
            ['operator01', 'Operator Gudang', 'operator01@example.com', 'operator', 'Operator12345'],
            ['operator02', 'Budi Santoso', 'budi.santoso@example.com', 'operator', 'Operator12345'],
            ['operator03', 'Joko Suyanto', 'joko.suyanto@example.com', 'operator', 'Operator12345'],
            ['operator04', 'Siti Rahayu', 'siti.rahayu@example.com', 'operator', 'Operator12345'],
        ];

        foreach ($users as [$username, $name, $email, $role, $password]) {
            User::firstOrCreate(
                ['username' => $username],
                ['name' => $name, 'email' => $email, 'role' => $role, 'password' => $password, 'is_locked' => false]
            );
        }
    }
}
