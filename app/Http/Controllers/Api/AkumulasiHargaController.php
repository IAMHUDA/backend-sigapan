<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkumulasiHarga;
use Carbon\Carbon;

class AkumulasiHargaController extends Controller
{
    /**
     * Tampilkan semua data akumulasi harga
     */
    public function index()
    {
        $data = AkumulasiHarga::orderBy('tanggal', 'desc')->get();
        return response()->json($data);
    }

    public function getByBahanPokok($id_bahan_pokok)
    {
        $data = AkumulasiHarga::where('id_bahan_pokok', $id_bahan_pokok)
                    ->orderBy('tanggal', 'desc')
                    ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Data akumulasi harga tidak ditemukan untuk bahan pokok ini'
            ], 404);
        }

        return response()->json($data);
    }

    /**
     * Simpan data akumulasi harga baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_bahan_pokok' => 'required|exists:bahan_pokok,id',
            'id_pasar'       => 'required|exists:pasar,id',
            'akumulasi_harga'=> 'required|numeric',
            'tanggal'        => 'nullable|date',
        ]);

        $validated['tanggal'] = $validated['tanggal'] ?? Carbon::today()->toDateString();

        $data = AkumulasiHarga::create($validated);

        return response()->json([
            'message' => 'Data akumulasi harga berhasil ditambahkan',
            'data' => $data
        ]);
    }

    /**
     * Tampilkan detail akumulasi harga
     */
    public function show($id)
    {
        $data = AkumulasiHarga::findOrFail($id);
        return response()->json($data);
    }

    /**
     * Update data akumulasi harga
     */
    public function update(Request $request, $id)
    {
        $data = AkumulasiHarga::findOrFail($id);

        $validated = $request->validate([
            'akumulasi_harga'=> 'nullable|numeric',
            'tanggal'        => 'nullable|date',
        ]);

        $data->update($validated);

        return response()->json([
            'message' => 'Data akumulasi harga berhasil diperbarui',
            'data' => $data
        ]);
    }

    /**
     * Hapus data akumulasi harga
     */
    public function destroy($id)
    {
        $data = AkumulasiHarga::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Data akumulasi harga berhasil dihapus']);
    }
}
