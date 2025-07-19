<?php

namespace App\Policies;

use App\Models\Pasar;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PasarPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat model apa pun.
     */
    public function viewAny(User $user): bool
    {
        // Admin atau Petugas Pasar dapat melihat daftar pasar
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat melihat model.
     */
    public function view(User $user, Pasar $pasar): bool
    {
        // Admin atau Petugas Pasar dapat melihat detail pasar
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat membuat model.
     */
    public function create(User $user): bool
    {
        // Admin atau Petugas Pasar dapat membuat pasar baru
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui model.
     */
    public function update(User $user, Pasar $pasar): bool
    {
        // Admin atau Petugas Pasar dapat memperbarui pasar
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus model.
     */
    public function delete(User $user, Pasar $pasar): bool
    {
        // Admin atau Petugas Pasar dapat menghapus pasar
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat mengembalikan (restore) model.
     */
    public function restore(User $user, Pasar $pasar): bool
    {
        return false;
    }

    /**
     * Tentukan apakah pengguna dapat secara permanen menghapus model.
     */
    public function forceDelete(User $user, Pasar $pasar): bool
    {
        return false;
    }
}

