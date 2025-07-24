<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PasarController extends Controller
{
    /**
     * Konstruktor untuk menerapkan kebijakan otorisasi.
     */
    public function __construct()
    {
        // Hanya Admin yang dapat mengelola data Pasar
        // $this->middleware('auth:sanctum');
        // Otorisasi menggunakan PasarPolicy
        // $this->authorizeResource(Pasar::class, 'pasar');
    }

    /**
     * Tampilkan daftar semua pasar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Diotorisasi oleh PasarPolicy@viewAny
        $pasar = Pasar::all();
        return response()->json($pasar);
    }

    /**
     * Simpan pasar baru ke penyimpanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Diotorisasi oleh PasarPolicy@create
        try {
            $validatedData = $request->validate([
                'nama' => 'required|string|max:255',
                'alamat' => 'nullable|string|max:255',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'jumlah_pedagang' => 'nullable|integer',
                'jumlah_kios' => 'nullable|integer',
                'jumlah_mck' => 'nullable|integer',
                'jumlah_bango' => 'nullable|integer',
                'jumlah_kantor' => 'nullable|integer',
                'tps' => 'nullable|string|max:255',
                'keterangan' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $data = $validatedData;

            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('foto_pasar', 'public');
                $data['foto'] = Storage::url($path);
            } else {
                $data['foto'] = null;
            }

            $pasar = Pasar::create($data);

            return response()->json($pasar, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pasar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan pasar yang ditentukan.
     *
     * @param  \App\Models\Pasar  $pasar
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Pasar $pasar)
    {
        // Diotorisasi oleh PasarPolicy@view
        return response()->json($pasar);
    }

    /**
     * Perbarui pasar yang ditentukan dalam penyimpanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pasar  $pasar
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Pasar $pasar)
    {
        // Diotorisasi oleh PasarPolicy@update
        try {
            $validatedData = $request->validate([   
                'nama' => 'required|string|max:255',
                'alamat' => 'nullable|string|max:255',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'jumlah_pedagang' => 'nullable|integer',
                'jumlah_kios' => 'nullable|integer',
                'jumlah_mck' => 'nullable|integer',
                'jumlah_bango' => 'nullable|integer',
                'jumlah_kantor' => 'nullable|integer',
                'tps' => 'nullable|string|max:255',
                'keterangan' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $data = $validatedData;

            if ($request->hasFile('foto')) {
                if ($pasar->foto) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $pasar->foto));
                }
                $path = $request->file('foto')->store('foto_pasar', 'public');
                $data['foto'] = Storage::url($path);
            } else if ($request->has('foto') && $request->input('foto') === null) {
                if ($pasar->foto) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $pasar->foto));
                }
                $data['foto'] = null;
            } else {
                unset($data['foto']);
            }

            $pasar->update($data);

            return response()->json($pasar);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui pasar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus pasar yang ditentukan dari penyimpanan.
     *
     * @param  \App\Models\Pasar  $pasar
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Pasar $pasar)
    {
        // Diotorisasi oleh PasarPolicy@delete
        try {
            if ($pasar->foto) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $pasar->foto));
            }
            $pasar->delete();
            return response()->json(['message' => 'Pasar berhasil dihapus.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus pasar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

