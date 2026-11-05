<?php

namespace App\Services;

use App\Models\BarangMasuk;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BarangMasukService
{
    private const RELATIONS = ['user:id,name,username', 'subCategory.category:id,kode,nama', 'items'];

    /** Query dasar + filter. Dipakai ulang oleh export Excel (Batch 7b). */
    public function filteredQuery(array $f, User $actor): Builder
    {
        $sortable = ['tanggal', 'asal_barang', 'total_harga'];
        $sortBy = in_array($f['sort_by'] ?? null, $sortable, true) ? $f['sort_by'] : 'tanggal';
        $dir = ($f['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        /** @var Builder<BarangMasuk> $query */
        $query = BarangMasuk::query()
            ->with(self::RELATIONS)
            ->withSum('items as total_harga', 'total')
            ->when($actor->role === 'operator', fn($q) => $q->where('user_id', $actor->id))
            ->when($f['category_id'] ?? null, fn($q, $id) => $q->whereHas(
                'subCategory',
                fn($s) => $s->where('category_id', $id)
            ))
            ->when($f['sub_category_id'] ?? null, fn($q, $id) => $q->where('sub_category_id', $id))
            ->when($f['year'] ?? null, fn($q, $y) => $q->whereYear('tanggal', $y))
            ->when($f['search'] ?? null, fn($q, $s) => $q->where(
                fn($w) => $w->where('asal_barang', 'ilike', "%$s%")
                    ->orWhereHas('subCategory', fn($x) => $x->where('nama', 'ilike', "%$s%"))
            ));

        return $query->orderBy($sortBy, $dir)->orderByDesc('id');
    }

    public function paginate(array $filters, User $actor, int $perPage = 10): LengthAwarePaginator
    {
        return $this->filteredQuery($filters, $actor)->paginate($perPage);
    }

    public function show(BarangMasuk $barangMasuk): BarangMasuk
    {
        return $barangMasuk->load(self::RELATIONS)->loadSum('items as total_harga', 'total');
    }

    public function create(array $data, ?UploadedFile $lampiran): BarangMasuk
    {
        return DB::transaction(function () use ($data, $lampiran) {
            $items = $this->prepareItems($data['items']);
            $this->assertWithinLimit((int) $data['sub_category_id'], $items);

            $barangMasuk = BarangMasuk::create([
                'user_id' => $data['user_id'],
                'sub_category_id' => $data['sub_category_id'],
                'tanggal' => now(),
                'asal_barang' => $data['asal_barang'],
                'nomor_surat' => $data['nomor_surat'] ?? null,
                'lampiran' => $lampiran?->store('lampiran', 'public'),
            ]);

            $barangMasuk->items()->createMany($items);

            return $this->show($barangMasuk);
        });
    }

    public function update(BarangMasuk $barangMasuk, array $data, ?UploadedFile $lampiran): BarangMasuk
    {
        return DB::transaction(function () use ($barangMasuk, $data, $lampiran) {
            $items = $this->prepareItems($data['items']);
            $this->assertWithinLimit((int) $data['sub_category_id'], $items);

            $attributes = [
                'user_id' => $data['user_id'],
                'sub_category_id' => $data['sub_category_id'],
                'asal_barang' => $data['asal_barang'],
                'nomor_surat' => $data['nomor_surat'] ?? null,
            ];

            if ($lampiran) {
                $this->deleteFile($barangMasuk->lampiran);
                $attributes['lampiran'] = $lampiran->store('lampiran', 'public');
            }

            $barangMasuk->update($attributes);

            $barangMasuk->items()->delete();
            $barangMasuk->items()->createMany($items);

            return $this->show($barangMasuk);
        });
    }

    public function delete(BarangMasuk $barangMasuk): void
    {
        DB::transaction(function () use ($barangMasuk) {
            $this->deleteFile($barangMasuk->lampiran);
            $barangMasuk->delete(); // items ikut terhapus (cascade)
        });
    }

    /** Total dihitung di server, bukan dipercaya dari frontend. */
    private function prepareItems(array $items): array
    {
        return array_map(fn($item) => [
            'nama_barang' => $item['nama_barang'],
            'harga' => (int) $item['harga'],
            'jumlah' => (int) $item['jumlah'],
            'satuan' => $item['satuan'],
            'total' => (int) $item['harga'] * (int) $item['jumlah'],
            'tgl_expired' => $item['tgl_expired'] ?? null,
        ], $items);
    }

    private function assertWithinLimit(int $subCategoryId, array $items): void
    {
        $limit = (int) SubCategory::findOrFail($subCategoryId)->batas_harga;
        $sum = array_sum(array_column($items, 'total'));

        if ($sum > $limit) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'Total harga (Rp %s) melebihi batas harga sub kategori (Rp %s)',
                    number_format($sum, 0, ',', '.'),
                    number_format($limit, 0, ',', '.')
                ),
            ]);
        }
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function toggleVerify(BarangMasuk $barangMasuk): BarangMasuk
    {
        $barangMasuk->update(['is_verified' => !$barangMasuk->is_verified]);

        return $this->show($barangMasuk);
    }

    public function exportRows(array $filters, User $actor): array
    {
        $rows = [];
        $no = 0;

        foreach ($this->filteredQuery($filters, $actor)->get() as $barangMasuk) {
            $no++;

            foreach ($barangMasuk->items as $i => $item) {
                $first = $i === 0;

                $rows[] = [
                    $first ? $no : '',
                    $first ? $barangMasuk->tanggal->format('d/m/Y H:i:s') : '',
                    $first ? $barangMasuk->asal_barang : '',
                    $first ? $barangMasuk->user->name : '',
                    $first ? $barangMasuk->subCategory->nama : '',
                    $barangMasuk->subCategory->category->kode . str_pad($item->id, 4, '0', STR_PAD_LEFT),
                    $item->nama_barang,
                    $item->harga,
                    $item->jumlah . ' ' . $item->satuan,
                    $item->total,
                    $first ? ($barangMasuk->is_verified ? 'Terverifikasi' : 'Belum') : '',
                ];
            }
        }

        return $rows;
    }
}