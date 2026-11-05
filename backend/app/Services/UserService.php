<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\BarangMasuk;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function paginate(?string $role, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->when($role, fn ($q, $r) => $q->where('role', $r))
            ->when($search, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('username', 'ilike', "%$s%")->orWhere('name', 'ilike', "%$s%")
            ))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (empty($data['password'])) {
            unset($data['password']); // kosong = password tidak diubah
        }

        $user->update($data);

        return $user;
    }

    public function toggleLock(User $user, User $actor): User
    {
        if ($user->id === $actor->id) {
            throw new BusinessRuleException('Tidak dapat mengunci akun sendiri');
        }

        $user->is_locked = !$user->is_locked;
        $user->save();

        if ($user->is_locked) {
            $user->tokens()->delete(); // paksa logout sesi yang sedang aktif
        }

        return $user;
    }

    public function delete(User $user, User $actor): void
    {
        if ($user->id === $actor->id) {
            throw new BusinessRuleException('Tidak dapat menghapus akun sendiri');
        }

        if (BarangMasuk::where('user_id', $user->id)->exists()) {
            throw new BusinessRuleException('User sudah memiliki data barang masuk, gunakan fitur kunci');
        }

        $user->tokens()->delete();
        $user->delete();
    }
}