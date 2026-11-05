<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->when($search, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('kode', 'ilike', "%$s%")->orWhere('nama', 'ilike', "%$s%")
            ))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        if ($category->subCategories()->exists()) {
            throw new BusinessRuleException('Kategori masih dipakai oleh sub kategori');
        }

        $category->delete();
    }
}