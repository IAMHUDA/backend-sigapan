<?php

namespace App\Policies;

use App\Models\HargaBapok;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HargaBapokPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat model apa pun.
     */
    public function viewAny(User $user): bool
    {
        // Admin dan Petugas Pasar dapat melihat semua harga bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat melihat model.
     */
    public function view(User $user, HargaBapok $hargaBapok): bool
    {
        // Admin dan Petugas Pasar dapat melihat detail harga bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat membuat model.
     */
    public function create(User $user): bool
    {
        // Admin dan Petugas Pasar dapat membuat entri harga bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui model.
     */
    public function update(User $user, HargaBapok $hargaBapok): bool
    {
        // Admin dan Petugas Pasar dapat memperbarui entri harga bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus model.
     */
    public function delete(User $user, HargaBapok $hargaBapok): bool
    {
        // Admin dan Petugas Pasar dapat menghapus entri harga bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat mengembalikan (restore) model.
     */
    public function restore(User $user, HargaBapok $hargaBapok): bool
    {
        return false;
    }

    /**
     * Tentukan apakah pengguna dapat secara permanen menghapus model.
     */
    public function forceDelete(User $user, HargaBapok $hargaBapok): bool
    {
        return false;
    }
}

