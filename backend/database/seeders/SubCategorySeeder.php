<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'PK' => [
                ['Alat Tulis', 500000],
                ['Kertas & Cetak', 2000000],
                ['Map & Arsip', 750000],
                ['Perekat & Penjepit', 300000],
                ['Papan & Alat Presentasi', 1500000],
                ['Perlengkapan Meja', 1000000],
            ],
            'EL' => [
                ['Elektronik Kantor', 15000000],
                ['Perangkat Audio Visual', 25000000],
                ['Peralatan Jaringan', 10000000],
                ['Baterai & Kabel', 1000000],
                ['Peralatan Listrik', 5000000],
            ],
            'FN' => [
                ['Meja & Kursi', 20000000],
                ['Lemari & Rak', 15000000],
                ['Perabotan Rapat', 25000000],
                ['Perabotan Lobi', 10000000],
                ['Perabotan Gudang', 7500000],
            ],
            'KB' => [
                ['Cairan Pembersih', 1500000],
                ['Alat Kebersihan', 1000000],
                ['Tisu & Sabun', 750000],
            ],
            'KS' => [
                ['Makanan Ringan', 1000000],
                ['Minuman', 1500000],
                ['Bahan Dapur', 2000000],
            ],
            'PD' => [
                ['Alat Masak', 3000000],
                ['Peralatan Makan', 1500000],
                ['Perlengkapan Pantry', 2000000],
            ],
            'KM' => [
                ['Laptop & PC', 50000000],
                ['Aksesoris Komputer', 3000000],
            ],
            'KL' => [
                ['Alat Pemadam', 5000000],
                ['Perlengkapan K3', 3000000],
            ],
            'PT' => [
                ['Perkakas Tangan', 2500000],
                ['Perkakas Listrik', 8000000],
            ],
            'PB' => [
                ['Kardus & Kemasan', 1200000],
                ['Plastik & Lakban', 600000],
            ],
            'BP' => [
                ['Buku & Majalah', 2000000],
            ],
            'SM' => [
                ['Seragam Kerja', 6000000],
            ],
        ];

        foreach ($data as $kode => $subs) {
            $category = Category::where('kode', $kode)->firstOrFail();

            foreach ($subs as [$nama, $batas]) {
                SubCategory::updateOrCreate(
                    ['category_id' => $category->id, 'nama' => $nama],
                    ['batas_harga' => $batas]
                );
            }
        }
    }
}
