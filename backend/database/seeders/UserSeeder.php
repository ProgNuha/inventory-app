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
            ['budisantoso', 'Budi Santoso', 'budi.santoso@example.com', 'operator', 'Operator12345'],
            ['jokosuyanto', 'Joko Suyanto', 'joko.suyanto@example.com', 'operator', 'Operator12345'],
            ['sitirahayu', 'Siti Rahayu', 'siti.rahayu@example.com', 'operator', 'Operator12345'],
        ];

        foreach ($users as [$username, $name, $email, $role, $password]) {
            // cari lewat email ATAU username supaya tidak bentrok dengan data lama
            $user = User::where('email', $email)->orWhere('username', $username)->first() ?? new User();

            $user->fill([
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'is_locked' => false,
            ]);

            if (!$user->exists) {
                $user->password = $password; // password hanya diisi untuk user baru
            }

            $user->save();
        }
    }
}
