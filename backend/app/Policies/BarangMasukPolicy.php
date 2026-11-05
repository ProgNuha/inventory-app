<?php

namespace App\Policies;

use App\Models\BarangMasuk;
use App\Models\User;

class BarangMasukPolicy
{
    public function view(User $user, BarangMasuk $barangMasuk): bool
    {
        return $user->role === 'admin' || $barangMasuk->user_id === $user->id;
    }

    public function update(User $user, BarangMasuk $barangMasuk): bool
    {
        return $this->view($user, $barangMasuk);
    }

    public function delete(User $user, BarangMasuk $barangMasuk): bool
    {
        return $this->view($user, $barangMasuk);
    }
}
