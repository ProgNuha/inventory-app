<?php

namespace Database\Seeders;

use App\Models\BarangMasuk;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BarangMasukSeeder extends Seeder
{
    private const ITEMS = [
        'Alat Tulis' => [['Kertas HVS A4 70Gr', 'Rim', 52000], ['Bolpoin Pilot Standard', 'Dus', 20000], ['Spidol Whiteboard', 'Kotak', 60000]],
        'Kertas & Cetak' => [['Kertas HVS A3', 'Rim', 85000], ['Tinta Printer', 'Botol', 95000], ['Kertas Thermal', 'Dus', 150000]],
        'Map & Arsip' => [['Map Snelhecter', 'Lusin', 48000], ['Ordner Folio', 'Buah', 32000], ['Box File', 'Buah', 28000]],
        'Perekat & Penjepit' => [['Lakban Bening', 'Roll', 12000], ['Paper Clip', 'Kotak', 6000], ['Stapler Set', 'Set', 35000]],
        'Papan & Alat Presentasi' => [['Whiteboard 120x90', 'Buah', 350000], ['Flipchart Stand', 'Unit', 450000], ['Laser Pointer', 'Buah', 85000]],
        'Perlengkapan Meja' => [['Desk Organizer', 'Buah', 75000], ['Alas Meja', 'Buah', 65000], ['Kalender Meja', 'Buah', 25000]],
        'Elektronik Kantor' => [['Mesin Hitung', 'Buah', 150000], ['Paper Shredder', 'Unit', 1800000], ['Mesin Laminating', 'Unit', 1200000]],
        'Perangkat Audio Visual' => [['Proyektor', 'Unit', 5500000], ['Speaker Meeting', 'Unit', 1750000], ['Layar Proyektor', 'Unit', 1200000]],
        'Peralatan Jaringan' => [['Switch 8 Port', 'Unit', 450000], ['Access Point', 'Unit', 850000], ['Kabel LAN Cat6', 'Roll', 650000]],
        'Baterai & Kabel' => [['Baterai AA Alkaline', 'Pack', 28000], ['Kabel Roll Extension', 'Buah', 85000], ['Stop Kontak', 'Buah', 45000]],
        'Peralatan Listrik' => [['Lampu LED 12W', 'Dus', 180000], ['Kipas Angin Berdiri', 'Unit', 350000], ['Stabilizer', 'Unit', 450000]],
        'Meja & Kursi' => [['Meja Kerja Staff', 'Unit', 1450000], ['Kursi Kerja Ergonomis', 'Unit', 1250000], ['Kursi Tamu', 'Unit', 650000]],
        'Lemari & Rak' => [['Lemari Arsip Besi', 'Unit', 2250000], ['Rak Buku 5 Susun', 'Unit', 850000], ['Filing Cabinet 4 Laci', 'Unit', 1950000]],
        'Perabotan Rapat' => [['Meja Rapat Oval', 'Unit', 5500000], ['Kursi Rapat', 'Unit', 950000], ['Kabinet Credenza', 'Unit', 2800000]],
        'Perabotan Lobi' => [['Sofa Tamu 3 Seater', 'Unit', 3200000], ['Meja Resepsionis', 'Unit', 2500000], ['Rak Majalah', 'Unit', 450000]],
        'Perabotan Gudang' => [['Rak Gudang Besi', 'Unit', 1100000], ['Pallet Plastik', 'Buah', 350000], ['Trolley Barang', 'Unit', 650000]],
        'Cairan Pembersih' => [['Pembersih Lantai 5L', 'Jerigen', 65000], ['Cairan Pembersih Kaca', 'Botol', 35000], ['Desinfektan 5L', 'Jerigen', 90000]],
        'Alat Kebersihan' => [['Sapu Ijuk', 'Buah', 25000], ['Pel Set', 'Set', 95000], ['Tempat Sampah', 'Buah', 55000]],
        'Tisu & Sabun' => [['Tisu Toilet', 'Dus', 120000], ['Sabun Cuci Tangan 5L', 'Jerigen', 85000], ['Tisu Wajah', 'Box', 15000]],
        'Makanan Ringan' => [['Biskuit Kaleng', 'Kaleng', 85000], ['Kacang Kulit', 'Pack', 25000], ['Wafer Cokelat', 'Dus', 70000]],
        'Minuman' => [['Air Mineral Galon', 'Galon', 22000], ['Kopi Sachet', 'Dus', 55000], ['Teh Celup', 'Kotak', 18000]],
        'Bahan Dapur' => [['Gula Pasir 1kg', 'Kg', 18000], ['Susu Krimer', 'Kaleng', 45000], ['Beras 5kg', 'Karung', 75000]],
        'Alat Masak' => [['Rice Cooker', 'Unit', 450000], ['Panci Set', 'Set', 275000], ['Kompor Gas Portable', 'Unit', 350000]],
        'Peralatan Makan' => [['Piring Set', 'Lusin', 120000], ['Gelas Kaca', 'Lusin', 85000], ['Sendok Garpu Set', 'Set', 95000]],
        'Perlengkapan Pantry' => [['Dispenser Galon', 'Unit', 450000], ['Termos Air Panas', 'Buah', 175000], ['Kulkas Mini', 'Unit', 1400000]],
        'Laptop & PC' => [['Laptop Core i5', 'Unit', 11500000], ['PC Desktop', 'Unit', 8500000], ['Monitor 24 Inch', 'Unit', 1950000]],
        'Aksesoris Komputer' => [['Mouse Wireless', 'Buah', 125000], ['Keyboard USB', 'Buah', 185000], ['Flashdisk 64GB', 'Buah', 85000]],
        'Alat Pemadam' => [['APAR 3kg', 'Unit', 650000], ['Fire Blanket', 'Buah', 275000], ['Smoke Detector', 'Buah', 185000]],
        'Perlengkapan K3' => [['Helm Safety', 'Buah', 55000], ['Sepatu Safety', 'Pasang', 285000], ['Rompi Reflektif', 'Buah', 45000]],
        'Perkakas Tangan' => [['Tang Kombinasi', 'Buah', 65000], ['Obeng Set', 'Set', 125000], ['Palu', 'Buah', 55000]],
        'Perkakas Listrik' => [['Bor Listrik', 'Unit', 750000], ['Gerinda Tangan', 'Unit', 650000], ['Solder Listrik', 'Buah', 95000]],
        'Kardus & Kemasan' => [['Kardus Double Wall', 'Bundle', 150000], ['Amplop Coklat', 'Pack', 45000], ['Bubble Wrap', 'Roll', 120000]],
        'Plastik & Lakban' => [['Plastik Wrap', 'Roll', 85000], ['Lakban Coklat', 'Roll', 14000], ['Kantong Plastik', 'Pack', 25000]],
        'Buku & Majalah' => [['Buku Agenda', 'Buah', 45000], ['Buku Folio', 'Dus', 130000], ['Buku Panduan Kantor', 'Eksemplar', 85000]],
        'Seragam Kerja' => [['Kemeja Seragam', 'Pcs', 185000], ['Jaket Kantor', 'Pcs', 275000], ['Name Tag', 'Pcs', 20000]],
    ];

    private const VENDORS = [
        'PT Sidomakmur',
        'PT Sejahtera',
        'CV Maju Jaya',
        'Toko ATK Berkah',
        'UD Sumber Rezeki',
        'PT Mitra Perkasa',
        'CV Karya Mandiri',
        'PT Global Teknindo',
        'Toko Bangunan Sentosa',
        'PT Cahaya Abadi',
        'CV Prima Utama',
        'UD Makmur Sentosa',
        'PT Nusantara Supplies',
        'Koperasi Karyawan',
        'Toko Elektronik Surya',
    ];

    private const ROMAN = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    private const EXPIRING = ['KS', 'KB'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // aman dijalankan berulang: jangan gandakan data
        if (BarangMasuk::query()->exists()) {
            return;
        }

        $operatorIds = User::where('role', 'operator')->pluck('id')->all();
        $subs = SubCategory::with('category')->get()->filter(fn($s) => isset(self::ITEMS[$s->nama]))->values();

        if (!$operatorIds || $subs->isEmpty()) {
            return;
        }

        mt_srand(2026); // hasil acak selalu sama

        $start = Carbon::create(2025, 1, 1);
        $span = (int) abs($start->diffInDays(Carbon::create(2026, 11, 4)));

        for ($n = 1; $n <= 100; $n++) {
            $sub = $subs[mt_rand(0, $subs->count() - 1)];
            $tanggal = $start->copy()->addDays(mt_rand(0, $span))->setTime(mt_rand(8, 16), mt_rand(0, 59), mt_rand(0, 59));
            $items = $this->buildItems($sub);

            $barang = BarangMasuk::create([
                'user_id' => $operatorIds[array_rand($operatorIds)],
                'sub_category_id' => $sub->id,
                'tanggal' => $tanggal,
                'asal_barang' => self::VENDORS[array_rand(self::VENDORS)],
                'nomor_surat' => mt_rand(1, 100) <= 70
                    ? sprintf('SM/%03d/%s/%d', $n, self::ROMAN[$tanggal->month], $tanggal->year)
                    : null,
                'lampiran' => null,
                'is_verified' => mt_rand(1, 100) <= 45,
            ]);

            $expiring = in_array($sub->category->kode, self::EXPIRING, true);

            foreach ($items as $item) {
                $barang->items()->create([
                    'nama_barang' => $item['nama'],
                    'harga' => $item['harga'],
                    'jumlah' => $item['jumlah'],
                    'satuan' => $item['satuan'],
                    'total' => $item['harga'] * $item['jumlah'],
                    'tgl_expired' => $expiring ? $tanggal->copy()->addMonths(mt_rand(6, 18))->toDateString() : null,
                ]);
            }
        }
    }

    private function buildItems(SubCategory $sub): array
    {
        $pool = self::ITEMS[$sub->nama];
        shuffle($pool);
        $pool = array_slice($pool, 0, mt_rand(1, min(4, count($pool))));

        $items = array_map(fn($p) => [
            'nama' => $p[0],
            'satuan' => $p[1],
            'harga' => max(1000, (int) round($p[2] * mt_rand(92, 108) / 100, -3)),
            'jumlah' => mt_rand(1, 8),
        ], $pool);

        $sum = fn() => array_sum(array_map(fn($i) => $i['harga'] * $i['jumlah'], $items));

        while ($sum() > $sub->batas_harga) {
            // kurangi jumlah pada item dengan total terbesar
            $idx = null;
            $max = 0;
            foreach ($items as $k => $i) {
                $t = $i['harga'] * $i['jumlah'];
                if ($i['jumlah'] > 1 && $t > $max) {
                    $max = $t;
                    $idx = $k;
                }
            }

            if ($idx !== null) {
                $items[$idx]['jumlah']--;
            } elseif (count($items) > 1) {
                array_pop($items);
            } else {
                break;
            }
        }

        return array_values($items);
    }
}
