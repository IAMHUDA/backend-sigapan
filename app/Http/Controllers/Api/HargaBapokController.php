<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HargaBapok;
use App\Models\BahanPokok;
use App\Models\Pasar;
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
        // $this->middleware('auth:sanctum');
        // $this->authorizeResource(HargaBapok::class, 'harga_bapok');
    }

    /**
     * Tampilkan daftar semua harga bahan pokok.
     */
    public function index(Request $request)
    {
        $query = HargaBapok::with(['pasar', 'bahanPokok'])
            ->orderBy('tanggal', 'desc');

        // Jika ada parameter id_pasar, filter berdasarkan pasar
        if ($request->has('id_pasar')) {
            $query->where('id_pasar', $request->id_pasar);
        }

        $hargaBapok = $query->get();

        return response()->json($hargaBapok);
    }

    /**
     * Simpan harga bahan pokok baru ke penyimpanan.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id_pasar' => 'required|exists:pasar,id',
                'id_bahan_pokok' => 'required|exists:bahan_pokok,id',
                'tanggal' => 'required|date',
                'harga' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
                'status_integrasi' => 'nullable|string|max:255',
            ]);

            // Cek apakah data sudah ada
            $existingData = HargaBapok::where('id_pasar', $validatedData['id_pasar'])
                ->where('id_bahan_pokok', $validatedData['id_bahan_pokok'])
                ->whereDate('tanggal', $validatedData['tanggal'])
                ->first();

            if ($existingData) {
                return response()->json([
                    'message' => 'Data untuk kombinasi pasar, bahan pokok, dan tanggal ini sudah ada.',
                    'existing_data' => $existingData,
                    'suggestion' => 'Gunakan endpoint PUT untuk memperbarui data yang sudah ada'
                ], 409); // 409 Conflict
            }

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
     */
    public function show(HargaBapok $harga_bapok)
    {
        $harga_bapok->load(['pasar', 'bahanPokok']);
        return response()->json($harga_bapok);
    }

    /**
     * Perbarui harga bahan pokok yang ditentukan dalam penyimpanan.
     */
    public function update(Request $request, HargaBapok $harga_bapok)
    {
        try {
            $validatedData = $request->validate([
                'id_pasar' => 'required|exists:pasar,id',
                'id_bahan_pokok' => 'required|exists:bahan_pokok,id',
                'tanggal' => 'required|date',
                'harga' => 'required|integer|min:0',
                'harga_baru' => 'required|integer|min:0',
                'stok' => 'required|integer|min:0',
                'status_integrasi' => 'nullable|string|max:255',
            ]);

            // Cek apakah update akan menyebabkan duplikasi dengan record lain
            $existingData = HargaBapok::where('id_pasar', $validatedData['id_pasar'])
                ->where('id_bahan_pokok', $validatedData['id_bahan_pokok'])
                ->whereDate('tanggal', $validatedData['tanggal'])
                ->where('id', '!=', $harga_bapok->id)
                ->first();

            if ($existingData) {
                return response()->json([
                    'message' => 'Data untuk kombinasi pasar, bahan pokok, dan tanggal ini sudah ada.',
                    'existing_data' => $existingData
                ], 409); // 409 Conflict
            }

            $harga_bapok->update($validatedData);

            // Hitung perubahan harga setelah update
            $hargaLama = (int) $harga_bapok->harga;
            $hargaBaru = (int) $harga_bapok->harga_baru;

            $perubahan = 'data tidak lengkap';
            $persen = null;

            if ($hargaLama > 0 && $hargaBaru > 0) {
                $selisih = $hargaBaru - $hargaLama;
                $persen = ($selisih / $hargaLama) * 100;

                if ($persen > 0.1) {
                    $perubahan = 'naik ' . number_format($persen, 2) . '%';
                } elseif ($persen < -0.1) {
                    $perubahan = 'turun ' . number_format(abs($persen), 2) . '%';
                } else {
                    $perubahan = 'tetap';
                }
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui.',
                'data' => $harga_bapok,
                'perubahan' => $perubahan,
                'persentase' => $persen !== null ? round($persen, 2) . '%' : null
            ]);
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
     */
    public function destroy(HargaBapok $harga_bapok)
    {
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

    /**
     * Tampilkan data harga bahan pokok dalam bentuk tabel sederhana.
     */
    public function table()
    {
        $data = HargaBapok::with(['pasar', 'bahanPokok'])
            ->orderBy('created_at', 'desc') // typo sebelumnya: create_at → created_at
            ->get()
            ->groupBy(fn($item) => $item->id_pasar . '-' . $item->id_bahan_pokok);

        $result = $data->map(function ($group) {
            $latest = $group->first();
            $stok = (int) $latest->stok;
            $stokWajib = (int) optional($latest->bahanPokok)->stok_wajib;
            $status = $stok >= $stokWajib ? 'tersedia' : 'perlu tambah stok';

            $hargaLama = (int) $latest->harga;
            $hargaBaru = (int) $latest->harga_baru;

            if ($hargaBaru > 0 && $hargaLama > 0) {
                $selisih = $hargaBaru - $hargaLama;
                $persen = ($selisih / $hargaLama) * 100;

                if ($persen > 0.1) {
                    $perubahan = ' naik ' . number_format($persen, 1) . '%';
                } elseif ($persen < -0.1) {
                    $perubahan = 'turun' . number_format(abs($persen), 1) . '%';
                } else {
                    $perubahan = 'tetap';
                }
            } else {
                $perubahan = 'data tidak lengkap';
            }

            return [
                'komoditas' => $latest->bahanPokok ? ucwords($latest->bahanPokok->nama) : null,
                'pasar' => $latest->pasar ? $latest->pasar->nama : null,
                'status' => $status,
                'stok' => $stok,
                'harga_lama' => $hargaLama,
                'harga_baru' => $hargaBaru,
                'perubahan' => $perubahan
            ];
        })->values();

        return response()->json($result);
    }


    /**
     * Tampilkan ringkasan harga terbaru dan perubahannya untuk semua bahan pokok.
     */
    public function summary()
    {
        $allBahanPokok = BahanPokok::all();
        $latestOverallDate = DB::table('harga_bapok')->max('tanggal');
        $summary = [];

        foreach ($allBahanPokok as $bahanPokok) {
            $latestPrices = DB::table('harga_bapok')
                ->where('id_bahan_pokok', $bahanPokok->id)
                ->orderBy('tanggal', 'desc')
                ->take(2)
                ->get();

            if ($latestPrices->count() < 1) {
                continue;
            }

            $priceTodayData = $latestPrices->first();
            $priceYesterdayData = $latestPrices->count() > 1 ? $latestPrices->last() : null;

            $avgPriceToday = $priceTodayData->harga;
            $avgPriceYesterday = $priceYesterdayData ? $priceYesterdayData->harga : $avgPriceToday;

            $priceChange = $avgPriceToday - $avgPriceYesterday;
            $percentageChange = ($avgPriceYesterday > 0)
                ? ($priceChange / $avgPriceYesterday) * 100
                : 0;

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
                'tanggal_terakhir' => $latestOverallDate,
                'change_status' => $changeStatus,
                'change_percent' => ($percentageChange > 0 ? '+' : '') . number_format($percentageChange, 2) . '%',
            ];
        }


        return response()->json([
            'tanggal_update' => $latestOverallDate,
            'data' => $summary
        ]);
    }
}
