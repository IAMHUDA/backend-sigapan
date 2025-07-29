<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Tampilkan semua user yang merupakan petugas pasar beserta data pasar terkait.
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'is_admin', 'is_petugas_pasar')
            ->where('is_petugas_pasar', true)
            ->get();

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
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'is_admin' => 'nullable|boolean',
            'is_petugas_pasar' => 'nullable|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil diperbarui.',
            'data' => $user
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
