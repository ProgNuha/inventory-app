<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['PK', 'Perlengkapan Kantor'],
            ['EL', 'Elektronik'],
            ['FN', 'Furnitur'],
            ['KB', 'Kebersihan'],
            ['KS', 'Konsumsi'],
            ['PD', 'Peralatan Dapur'],
            ['KM', 'Komputer & Aksesoris'],
            ['KL', 'Keamanan & K3'],
            ['PT', 'Perkakas & Teknik'],
            ['PB', 'Pembungkus & Kemasan'],
            ['BP', 'Buku & Percetakan'],
            ['SM', 'Seragam & Atribut'],
            ['KD', 'Kendaraan & Suku Cadang'],
            ['MD', 'Peralatan Medis'],
            ['BB', 'Bahan Bangunan'],
        ];

        foreach ($categories as [$kode, $nama]) {
            Category::updateOrCreate(['kode' => $kode], ['nama' => $nama]);
        }
    }
}
