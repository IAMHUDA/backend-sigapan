<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Pasar;

class UserController extends Controller
{
    /**
     * Tampilkan semua user yang merupakan petugas pasar beserta data pasar terkait.
     */
    public function index()
    {
        $users = User::with('pasar') // relasi pasar, ambil hanya kolom id dan nama
            ->select('id', 'name', 'email', 'is_admin', 'is_petugas_pasar', 'id_pasar','no_telepon')
            ->where('is_petugas_pasar', true)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'is_petugas_pasar' => $user->is_petugas_pasar,
                    'id_pasar' => $user->id_pasar,
                    'nama_pasar' => $user->pasar?->nama ?? null, 
                    'no_telepon' => $user->no_telepon ,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar petugas pasar berhasil diambil.',
            'data' => $users
        ]);
    }


    /**
     * Tampilkan detail user tertentu.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail user berhasil diambil.',
            'data' => $user
        ]);
    }

    /**
     * Update data user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validasi input
        $validated = $request->validate([
            'name'              => 'sometimes|required|string|max:255',
            'email'             => 'sometimes|required|email|unique:users,email,' . $id,
            'is_admin'          => 'nullable|boolean',
            'is_petugas_pasar'  => 'nullable|boolean',
            'id_pasar'          => 'nullable|exists:pasar,id', 
            'no_telepon' => 'required|string|min:10|max:15|regex:/^08[0-9]{8,12}$/',
        ]);

        $user->update($validated);

        // Muat ulang relasi pasar setelah update untuk mendapatkan nama_pasar yang terbaru jika perlu
        $user->load('pasar:id,nama');

        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'is_petugas_pasar' => $user->is_petugas_pasar,
                'id_pasar' => $user->id_pasar,
                'nama_pasar' => $user->pasar?->nama ?? null, 
            ]
        ]);
    }


    /**
     * Hapus user.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
    /**
     * Tampilkan user yang sedang login
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'User profile berhasil diambil.',
            'data' => $request->user()
        ]);
    }
}
