<?php

namespace App\Services;

use App\Models\BarangMasuk;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OptionService
{
    public function categories(): Collection
    {
        return Category::orderBy('nama')->get(['id', 'kode', 'nama']);
    }

    public function subCategories(?int $categoryId): Collection
    {
        return SubCategory::when($categoryId, fn($q, $id) => $q->where('category_id', $id))
            ->orderBy('nama')
            ->get(['id', 'category_id', 'nama', 'batas_harga']);
    }

    public function operators(): Collection
    {
        return User::where('role', 'operator')
            ->where('is_locked', false)
            ->orderBy('name')
            ->get(['id', 'name', 'username']);
    }

    public function years(): array
    {
        return BarangMasuk::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM tanggal)::int AS year')
            ->orderByDesc('year')
            ->pluck('year')
            ->all();
    }
}