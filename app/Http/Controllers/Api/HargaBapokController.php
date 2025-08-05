<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HargaBapok;
use App\Models\BahanPokok;
use App\Models\Pasar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HargaBapokController extends Controller
{
    /**
     * Konstruktor untuk menerapkan kebijakan otorisasi.
     */
    public function __construct()
    {
        // Hanya Admin dan Petugas Pasar yang dapat mengelola data HargaBapok
        // $this->middleware('auth:sanctum');
        // Otorisasi menggunakan HargaBapokPolicy
        // $this->authorizeResource(HargaBapok::class, 'harga_bapok');
    }

    /**
     * Tampilkan daftar semua harga bahan pokok.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Diotorisasi oleh HargaBapokPolicy@viewAny
        $hargaBapok = HargaBapok::with(['pasar', 'bahanPokok'])->orderBy('tanggal', 'desc')->get();
        return response()->json($hargaBapok);
    }


    /**
     * Simpan harga bahan pokok baru ke penyimpanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Diotorisasi oleh HargaBapokPolicy@create
        try {
            $validatedData = $request->validate([
                'id_pasar' => 'required|exists:pasar,id',
                'id_bahan_pokok' => 'required|exists:bahan_pokok,id',
                'tanggal' => 'required|date',
                'harga' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'status_integrasi' => 'nullable|string|max:255',
            ]);

            // Mengisi created_by dengan email pengguna yang sedang login
            $validatedData['created_by'] = Auth::user()->name;

            $hargaBapok = HargaBapok::create($validatedData);

            return response()->json($hargaBapok, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan harga bahan pokok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan harga bahan pokok yang ditentukan.
     *
     * @param  \App\Models\HargaBapok  $harga_bapok
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(HargaBapok $harga_bapok)
    {
        // Diotorisasi oleh HargaBapokPolicy@view
        $harga_bapok->load(['pasar', 'bahanPokok']);
        return response()->json($harga_bapok);
    }

    /**
     * Perbarui harga bahan pokok yang ditentukan dalam penyimpanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HargaBapok  $harga_bapok
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, HargaBapok $harga_bapok)
    {
        // Diotorisasi oleh HargaBapokPolicy@update
        try {
            $validatedData = $request->validate([
                'id_pasar' => 'required|exists:pasar,id',
                'id_bahan_pokok' => 'required|exists:bahan_pokok,id',
                'tanggal' => 'required|date',
                'harga' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'status_integrasi' => 'nullable|string|max:255',
            ]);

            $harga_bapok->update($validatedData);

            return response()->json($harga_bapok);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui harga bahan pokok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus harga bahan pokok yang ditentukan dari penyimpanan.
     *
     * @param  \App\Models\HargaBapok  $harga_bapok
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(HargaBapok $harga_bapok)
    {
        // Diotorisasi oleh HargaBapokPolicy@delete
        try {
            $harga_bapok->delete();
            return response()->json(['message' => 'Harga bahan pokok berhasil dihapus.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus harga bahan pokok.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function summary()
    {
        $allBahanPokok = BahanPokok::all();

        $summary = [];

        foreach ($allBahanPokok as $bahanPokok) {
            // 2. Untuk setiap bahan pokok, ambil dua harga terbaru
            $latestPrices = DB::table('harga_bapok')
                ->where('id_bahan_pokok', $bahanPokok->id)
                ->orderBy('tanggal', 'desc')
                ->take(2)
                ->get();

            // Jika tidak ada data harga sama sekali untuk komoditas ini, lewati
            if ($latestPrices->count() < 1) {
                continue;
            }

            // Atur harga hari ini dan kemarin (dari data terbaru)
            $priceTodayData = $latestPrices->first();
            $priceYesterdayData = $latestPrices->count() > 1 ? $latestPrices->last() : null;

            $avgPriceToday = $priceTodayData->harga;
            $avgPriceYesterday = $priceYesterdayData ? $priceYesterdayData->harga : $avgPriceToday;

            $priceChange = $avgPriceToday - $avgPriceYesterday;
            $percentageChange = ($avgPriceYesterday > 0) ? ($priceChange / $avgPriceYesterday) * 100 : 0;

            $changeStatus = 'same';
            if ($percentageChange > 0.01) {
                $changeStatus = 'up';
            } elseif ($percentageChange < -0.01) {
                $changeStatus = 'down';
            }

            $summary[] = [
                'id' => $bahanPokok->id,
                'name' => $bahanPokok->nama,
                'image_url' => $bahanPokok->foto,
                'unit' => $bahanPokok->satuan,
                'price' => round($avgPriceToday),
                'change_status' => $changeStatus,
                'change_percent' => ($percentageChange > 0 ? '+' : '') . number_format($percentageChange, 2) . '%',
            ];
        }

        // Tentukan tanggal update dari harga terbaru secara keseluruhan
        $latestOverallDate = DB::table('harga_bapok')->max('tanggal');

        return response()->json([
            'tanggal_update' => $latestOverallDate,
            'data' => $summary
        ]);
    }
}
