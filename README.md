**Live App:** https://inventory-app-ac92.onrender.com/

> Aplikasi di-hosting pada Render (free tier). Apabila tidak diakses selama ±15 menit, layanan berada dalam kondisi tidur sehingga **akses pertama memerlukan waktu ±1 menit** (cold start). Mohon menunggu hingga halaman login ditampilkan.

# Aplikasi Pengelolaan Barang

Tes teknis Fullstack Programmer JMC Indonesia: aplikasi untuk mengelola data **Barang Masuk** beserta data master Kategori, Sub Kategori, dan User.

| | |
|---|---|
| **Link Deployment** | https://inventory-app-ac92.onrender.com/ |
| **Link Video Demo** | https://bit.ly/4fdjE81 |

## Tech Stack

| Lapisan | Teknologi |
|---|---|
| Frontend | Vue 3 (JavaScript, `<script setup>`), Vite, Tailwind CSS v4, Pinia, Vue Router, Axios |
| Backend | Laravel 12 (PHP 8.3), Laravel Sanctum (token), maatwebsite/excel (export), barryvdh/dompdf (PDF) |
| Database | PostgreSQL 16 (lokal menggunakan Docker, produksi menggunakan Supabase) |
| Deployment | Docker (satu container untuk backend dan frontend pada satu port), Render, Supabase |

Arsitektur backend: validasi pada **FormRequest**, logika bisnis pada **Service**, **Controller** hanya menangani HTTP request dan response, serta otorisasi melalui **Policy**.

## Akun Pengujian

| Role | Username | Password |
|---|---|---|
| Admin | `demoadmin` | `DemoAdmin123` |
| Operator | `demooperator` | `DemoOperator123` |

Akun lain yang tersedia dari seeder: `administrator` / `Admin12345` (admin), serta `operator01`, `budisantoso`, dan `jokosuyanto` / `Operator12345` (operator).

Akun **terkunci** untuk pengujian fitur kunci user: `sitirahayu` / `Operator12345`. Login menggunakan akun ini ditolak dengan pesan "Akun Anda terkunci, hubungi admin". Kunci dapat dibuka oleh admin melalui menu User.

Hak akses:
- **Admin:** seluruh menu (Barang Masuk, Kategori, Sub Kategori, User), verifikasi barang masuk, dan melihat seluruh data.
- **Operator:** hanya menu Barang Masuk, dengan akses terbatas pada data miliknya sendiri.

## Fitur

- **Login** menggunakan token (Sanctum). Akun yang dikunci tidak dapat masuk dan seluruh tokennya dihapus.
- **Barang Masuk:** daftar 10 data per halaman; filter Kategori → Sub Kategori (bertingkat) → Tahun; pencarian berdasarkan asal barang atau sub kategori; pengurutan berdasarkan tanggal, asal barang, atau total harga; export Excel sesuai filter dan pencarian; cetak surat masuk (PDF) per baris; verifikasi (admin).
- **Form Barang Masuk:** operator, kategori, sub kategori, batas harga, asal barang, nomor surat, lampiran (doc/docx/zip, maksimal 5 MB), dan item dinamis (nama, harga, jumlah, satuan, total, tanggal expired). **Total seluruh item tidak boleh melebihi batas harga sub kategori** (divalidasi pada frontend dan backend).
- **Master Kategori** dan **Sub Kategori** (admin).
- **Manajemen User** (admin): pengaturan role, kunci dan buka kunci akun, pembatasan agar user tidak dapat mengunci atau menghapus akunnya sendiri, serta password yang dikosongkan saat edit berarti tidak diubah.

## Instalasi Lokal

### Opsi 1: Docker (direkomendasikan)

Prasyarat: Docker Desktop.

```bash
git clone https://github.com/ProgNuha/inventory-app
cd inventory-app
docker compose up --build
```

Aplikasi dapat diakses melalui http://localhost:8000. `APP_KEY`, migrasi, dan seeder dijalankan secara otomatis saat container dimulai, sehingga pembuatan file `.env` tidak diperlukan. (Opsional: salin `.env.example` menjadi `.env` dan isi `APP_KEY` apabila key perlu tetap sama setelah container dibuat ulang.)

Apabila terjadi error `password authentication failed`, hapus volume database kemudian jalankan kembali:

```bash
docker compose down -v
docker compose up --build
```

### Opsi 2: Tanpa Docker (mode pengembangan)

Prasyarat: PHP 8.2 atau lebih baru (ekstensi `pdo_pgsql`, `gd`, `zip`, `mbstring`), Composer, Node.js 22, dan PostgreSQL.

```bash
# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database pada `backend/.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventory
DB_USERNAME=postgres
DB_PASSWORD=
APP_TIMEZONE=Asia/Jakarta
```

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve            # http://127.0.0.1:8000
```

```bash
# Frontend (pada terminal terpisah)
cd frontend
npm install
npm run dev                  # http://localhost:5173 (proxy /api dan /storage ke :8000)
```

### Data Seeder

Seeder menyediakan 15 kategori, 35 sub kategori, 100 data barang masuk (Januari 2025 sampai November 2026), dan 7 akun pengujian (1 di antaranya terkunci). Seeder aman dijalankan berulang. Untuk mengosongkan dan mengisi ulang database, jalankan `php artisan migrate:fresh --seed` atau atur environment variable `FRESH_SEED=true` pada container.
