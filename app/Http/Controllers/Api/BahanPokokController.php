<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\BahanPokok;
use Illuminate\Support\Facades\Storage; // Untuk mengelola file foto
use Illuminate\Validation\ValidationException; // Penting untuk menangani validasi

class BahanPokokController extends Controller
{
    use AuthorizesRequests;
    /**
     * Konstruktor untuk menerapkan kebijakan otorisasi.
     */
    public function __construct()
    {
        // Middleware 'auth:sanctum' diterapkan pada rute POST, PUT, PATCH, DELETE di api.php
        // Metode 'index' dan 'show' sudah publik di api.php.
        // Otorisasi melalui policy akan tetap berjalan untuk semua metode.
        // $this->middleware('auth:sanctum'); // Ini dipindahkan ke routes/api.php
        // $this->authorizeResource(BahanPokok::class, 'bahan_pokok'); // Ini juga dipindahkan ke routes/api.php
    }

    /**
     * Tampilkan daftar semua bahan pokok.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Metode ini diakses publik, tidak memerlukan autentikasi.
        // Policy BahanPokokPolicy@viewAny akan tetap dijalankan jika rute ini dilindungi di masa depan.
        $bahanPokok = BahanPokok::orderBy('urutan')->get();
        return response()->json($bahanPokok);
    }

    /**
     * Tampilkan bahan pokok yang ditentukan.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Metode ini diakses publik, tidak memerlukan autentikasi.
        // Policy BahanPokokPolicy@view akan tetap dijalankan jika rute ini dilindungi di masa depan.
        $item = BahanPokok::findOrFail($id);
        return response()->json($item);
    }

    /**
     * Simpan bahan pokok baru ke penyimpanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        // Metode ini akan diotorisasi oleh BahanPokokPolicy@create
        $this->authorize('create', BahanPokok::class);

        try {
            $validatedData = $request->validate([
                'id_api_bahan_pokok' => 'required|integer', 
                'urutan' => 'nullable|integer',
                'nama' => 'required|string|max:50',
                'satuan' => 'required|string|max:10',
                'up_stok' => 'required|boolean',
                'stok_wajib' => 'required|boolean',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Inisialisasi $data dengan $validatedData terlebih dahulu
            $data = $validatedData;

            if ($request->hasFile('foto')) {
                // Simpan di storage/app/public/foto_bahan_pokok
                $path = $request->file('foto')->store('foto_bahan_pokok', 'public');
                $data['foto'] = Storage::url($path); // Dapatkan URL yang dapat diakses publik
            } else {
                // Jika tidak ada foto diunggah, pastikan kolom foto di database null
                $data['foto'] = null;
            }

            $bahan = BahanPokok::create($data); // Gunakan $data yang sudah lengkap

            return response()->json($bahan, 201); // 201 Created
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan bahan pokok.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    /**
     * Perbarui bahan pokok yang ditentukan dalam penyimpanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Metode ini akan diotorisasi oleh BahanPokokPolicy@update
        $bahan = BahanPokok::findOrFail($id);
        $this->authorize('update', $bahan);

        try {
            $validatedData = $request->validate([
                'id_api_bahan_pokok' => 'required|integer',
                'urutan' => 'nullable|integer',
                'nama' => 'required|string|max:50',
                'satuan' => 'nullable|string|max:10',
                'up_stok' => 'boolean', 
                'stok_wajib' => 'boolean', 
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Max 2MB
            ]);

            $data = $validatedData; // Inisialisasi $data dengan $validatedData

            // Hapus foto lama jika ada dan ada foto baru diunggah
            if ($request->hasFile('foto')) {
                if ($bahan->foto) {
                    // Pastikan path yang dihapus sesuai dengan yang disimpan
                    Storage::disk('public')->delete(str_replace('/storage/', '', $bahan->foto));
                }
                $path = $request->file('foto')->store('foto_bahan_pokok', 'public');
                $data['foto'] = Storage::url($path);
            } else if ($request->has('foto') && $request->input('foto') === null) {
                // Jika input foto dikirim null secara eksplisit, berarti ingin menghapus foto yang sudah ada
                if ($bahan->foto) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $bahan->foto));
                }
                $data['foto'] = null;
            } else {
                // Jika tidak ada file baru dan tidak ada permintaan hapus eksplisit, pertahankan foto lama
                unset($data['foto']); // Jangan masukkan 'foto' ke $data jika tidak berubah
            }

            $bahan->update($data); // Gunakan $data yang sudah lengkap

            return response()->json($bahan);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui bahan pokok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus bahan pokok yang ditentukan dari penyimpanan.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Metode ini akan diotorisasi oleh BahanPokokPolicy@delete
        try {
            $bahan = BahanPokok::findOrFail($id);
            $this->authorize('delete', $bahan);//policy di sini
            if ($bahan->foto) {
                // Pastikan path yang dihapus sesuai dengan yang disimpan
                Storage::disk('public')->delete(str_replace('/storage/', '', $bahan->foto));
            }
            $bahan->delete();
            return response()->json(['message' => 'Data berhasil dihapus'], 204); 
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus bahan pokok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

