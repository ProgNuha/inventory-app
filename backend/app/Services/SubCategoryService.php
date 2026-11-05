<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\BarangMasuk;
use App\Models\SubCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class SubCategoryService
{
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return SubCategory::with('category:id,kode,nama')
            ->when($search, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('nama', 'ilike', "%$s%")
                    ->orWhereHas('category', fn ($c) => $c->where('nama', 'ilike', "%$s%"))
            ))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function create(array $data): SubCategory
    {
        return SubCategory::create($data)->load('category:id,kode,nama');
    }

    public function update(SubCategory $subCategory, array $data): SubCategory
    {
        $subCategory->update($data);

        return $subCategory->load('category:id,kode,nama');
    }

    public function delete(SubCategory $subCategory): void
    {
        if (BarangMasuk::where('sub_category_id', $subCategory->id)->exists()) {
            throw new BusinessRuleException('Sub kategori sudah dipakai pada data barang masuk');
        }

        $subCategory->delete();
    }
}