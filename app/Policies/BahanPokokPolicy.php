<?php

namespace App\Policies;

use App\Models\BahanPokok;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BahanPokokPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat model apa pun.
     */
    public function viewAny(User $user): bool
    {
        // Semua pengguna terautentikasi (admin atau petugas pasar) dapat melihat daftar bahan pokok
        // Catatan: Rute GET /bahan-pokok sudah publik, jadi policy ini hanya relevan jika ada middleware 'auth:sanctum'
        // pada rute GET di masa depan, atau untuk konsistensi.
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat melihat model.
     */
    public function view(User $user, BahanPokok $bahanPokok): bool
    {
        // Semua pengguna terautentikasi dapat melihat detail bahan pokok
        // Catatan: Rute GET /bahan-pokok/{id} sudah publik.
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat membuat model.
     */
    public function create(User $user): bool
    {
        // ADMIN atau PETUGAS PASAR dapat membuat bahan pokok baru
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui model.
     */
    public function update(User $user): bool
    {
        // ADMIN atau PETUGAS PASAR dapat memperbarui bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus model.
     */
    public function delete(User $user): bool
    {
        // ADMIN atau PETUGAS PASAR dapat menghapus bahan pokok
        return $user->is_admin || $user->is_petugas_pasar;
    }

    /**
     * Tentukan apakah pengguna dapat mengembalikan (restore) model.
     */
    public function restore(User $user, BahanPokok $bahanPokok): bool
    {
        // Tidak relevan untuk kasus ini
        return false;
    }

    /**
     * Tentukan apakah pengguna dapat secara permanen menghapus model.
     */
    public function forceDelete(User $user, BahanPokok $bahanPokok): bool
    {
        // Tidak relevan untuk kasus ini
        return false;
    }
}

