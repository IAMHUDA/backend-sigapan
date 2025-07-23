<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tangani permintaan login pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Coba autentikasi pengguna
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        $user = Auth::user();

        // Hapus token lama jika ada (opsional, tergantung kebutuhan)
        // $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'is_petugas_pasar' => $user->is_petugas_pasar,
            ]
        ]);
    }

    /**
     * Tangani permintaan logout pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Hapus token saat ini yang digunakan untuk autentikasi
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil!'
        ]);
    }
    /**
     * Tangani permintaan registrasi pengguna baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_admin' => ['nullable', 'boolean'],
            'is_petugas_pasar' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => $request->is_admin ?? false,
            'is_petugas_pasar' => $request->is_petugas_pasar ?? false,
        ]);

        return response()->json([
            'message' => 'Pengguna berhasil dibuat.',
            'user' => $user,
        ], 201);
    }


    /**
     * Update profil pengguna yang sedang login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
        'is_admin' => ['nullable', 'boolean'],
        'is_petugas_pasar' => ['nullable', 'boolean'],
    ]);

    $user->update($request->only('name', 'email', 'is_admin', 'is_petugas_pasar'));

    return response()->json([
        'message' => 'User berhasil diperbarui.',
        'data' => $user
    ]);
}

public function show($id)
{
    $user = User::findOrFail($id);

    return response()->json([
        'data' => $user,
    ]);
}


    /**
     * Hapus akun pengguna yang sedang login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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

public function index()
{
    return response()->json([
        'data' => User::select('id', 'name', 'email', 'is_admin', 'is_petugas_pasar')->get(),
    ]);
}
    /**
     * Dapatkan detail pengguna yang sedang diautentikasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'is_admin' => $request->user()->is_admin,
                'is_petugas_pasar' => $request->user()->is_petugas_pasar,
            ]
        ]);
    }
}


