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
            ['demoadmin', 'Demo Admin', 'demo.admin@example.com', 'admin', 'DemoAdmin123', false],
            ['demooperator', 'Demo Operator', 'demo.operator@example.com', 'operator', 'DemoOperator123', false],
            ['administrator', 'Administrator', 'admin@example.com', 'admin', 'Admin12345', false],
            ['operator01', 'Operator Gudang', 'operator01@example.com', 'operator', 'Operator12345', false],
            ['budisantoso', 'Budi Santoso', 'budi.santoso@example.com', 'operator', 'Operator12345', false],
            ['jokosuyanto', 'Joko Suyanto', 'joko.suyanto@example.com', 'operator', 'Operator12345', false],
            ['sitirahayu', 'Siti Rahayu', 'siti.rahayu@example.com', 'operator', 'Operator12345', true], // dikunci untuk demo
        ];

        foreach ($users as [$username, $name, $email, $role, $password, $locked]) {
            $user = User::where('email', $email)->orWhere('username', $username)->first() ?? new User();

            $user->fill([
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'role' => $role,
            ]);

            if (!$user->exists) {
                $user->password = $password;
                $user->is_locked = $locked;
            }

            $user->save();
        }
    }
}
