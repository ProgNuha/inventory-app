<?php

namespace Database\Seeders;

use App\Models\{User, Category, SubCategory};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */


    public function run(): void
    {
        User::updateOrCreate(['username' => 'administrator'], [
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin12345'),
            'role' => 'admin',
            'is_locked' => false,
        ]);

        $operators = [
            ['username' => 'operator01', 'name' => 'Operator Gudang', 'email' => 'operator@example.com'],
            ['username' => 'budisantoso', 'name' => 'Budi Santoso', 'email' => 'budi.santoso@example.com'],
            ['username' => 'jokosuyanto', 'name' => 'Joko Suyanto', 'email' => 'joko.suyanto@example.com'],
            ['username' => 'sitirahayu', 'name' => 'Siti Rahayu', 'email' => 'siti.rahayu@example.com'],
        ];

        foreach ($operators as $op) {
            User::updateOrCreate(['username' => $op['username']], [
                'name' => $op['name'],
                'email' => $op['email'],
                'password' => Hash::make('Operator12345'),
                'role' => 'operator',
                'is_locked' => false,
            ]);
        }

        $pk = Category::updateOrCreate(['kode' => 'PK'], ['nama' => 'Perlengkapan Kantor']);
        $fo = Category::updateOrCreate(['kode' => 'FO'], ['nama' => 'Makanan']);

        SubCategory::updateOrCreate(['category_id' => $pk->id, 'nama' => 'Alat Tulis'], ['batas_harga' => 500000]);
        SubCategory::updateOrCreate(['category_id' => $pk->id, 'nama' => 'Perabotan'], ['batas_harga' => 7500000]);
        SubCategory::updateOrCreate(['category_id' => $fo->id, 'nama' => 'Makanan Ringan'], ['batas_harga' => 1000000]);
    }
}
