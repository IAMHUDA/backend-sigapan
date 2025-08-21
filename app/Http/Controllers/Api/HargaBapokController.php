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
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
        // $this->authorizeResource(HargaBapok::class, 'harga_bapok');
    }


    public function index(Request $request)
    {
        $query = HargaBapok::with(['pasar', 'bahanPokok'])
            ->orderBy('tanggal', 'desc');

        if ($request->has('id_pasar')) {
            $query->where('id_pasar', $request->id_pasar);
        }

        $hargaBapok = $query->get();

        return response()->json($hargaBapok);
    }

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

            $existingData = HargaBapok::where('id_pasar', $validatedData['id_pasar'])
                ->where('id_bahan_pokok', $validatedData['id_bahan_pokok'])
                ->whereDate('tanggal', $validatedData['tanggal'])
                ->first();

            if ($existingData) {
                return response()->json([
                    'message' => 'Data untuk kombinasi pasar, bahan pokok, dan tanggal ini sudah ada.',
                    'existing_data' => $existingData,
                    'suggestion' => 'Gunakan endpoint PUT untuk memperbarui data yang sudah ada'
                ], 409);
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

    public function show(HargaBapok $harga_bapok)
    {
        $harga_bapok->load(['pasar', 'bahanPokok']);
        return response()->json($harga_bapok);
    }

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

            $existingData = HargaBapok::where('id_pasar', $validatedData['id_pasar'])
                ->where('id_bahan_pokok', $validatedData['id_bahan_pokok'])
                ->whereDate('tanggal', $validatedData['tanggal'])
                ->where('id', '!=', $harga_bapok->id)
                ->first();

            if ($existingData) {
                return response()->json([
                    'message' => 'Data untuk kombinasi pasar, bahan pokok, dan tanggal ini sudah ada.',
                    'existing_data' => $existingData
                ], 409);
            }

            $harga_bapok->update($validatedData);

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

    public function table()
    {
        $latestPrices = HargaBapok::query()
            ->select('id_pasar', 'id_bahan_pokok', DB::raw('MAX(tanggal) as latest_tanggal'))
            ->whereNotNull('harga')
            ->where('harga', '>', 0)
            ->groupBy('id_pasar', 'id_bahan_pokok');

        $data = HargaBapok::with(['pasar', 'bahanPokok'])
            ->joinSub($latestPrices, 'latest_prices', function ($join) {
                $join->on('harga_bapok.id_pasar', '=', 'latest_prices.id_pasar')
                    ->on('harga_bapok.id_bahan_pokok', '=', 'latest_prices.id_bahan_pokok')
                    ->on('harga_bapok.tanggal', '=', 'latest_prices.latest_tanggal');
            })
            ->orderBy('harga_bapok.tanggal', 'desc')
            ->get();

        $result = $data->map(function ($item) {
            $stok = (int) $item->stok;
            $stokWajib = (int) optional($item->bahanPokok)->stok_wajib;
            $status = $stok >= $stokWajib ? 'Tersedia' : 'Terbatas';

            $hargaBaru = (int) $item->harga;

            $hargaSebelumnya = HargaBapok::where('id_pasar', $item->id_pasar)
                ->where('id_bahan_pokok', $item->id_bahan_pokok)
                ->where('tanggal', '<', $item->tanggal)
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->value('harga');

            $changeStatus = 'same';
            $percentageChange = 0;

            if ($hargaSebelumnya > 0 && $hargaBaru > 0) {
                $selisih = $hargaBaru - $hargaSebelumnya;
                $persen = ($selisih / $hargaSebelumnya) * 100;

                if ($persen > 0.1) {
                    $changeStatus = 'up';
                    $percentageChange = $persen;
                } elseif ($persen < -0.1) {
                    $changeStatus = 'down';
                    $percentageChange = abs($persen);
                } else {
                    $changeStatus = 'same';
                    $percentageChange = 0;
                }
            } else {
                $changeStatus = 'N/A';
                $percentageChange = 0;
            }

            return [
                'komoditas' => $item->bahanPokok ? ucwords($item->bahanPokok->nama) : null,
                'pasar' => $item->pasar ? $item->pasar->nama : null,
                'status' => $status,
                'stok' => $stok,
                'harga' => $hargaBaru,
                'tanggal' => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                'change_status' => $changeStatus,
                'change_percent' => number_format($percentageChange, 2),
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
        $summary = [];

        foreach ($allBahanPokok as $bahanPokok) {

            $latestPrices = DB::table('harga_bapok')
                ->where('id_bahan_pokok', $bahanPokok->id)
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->take(2)
                ->get();

            if ($latestPrices->count() < 1) {
                continue;
            }

            $priceTodayData = $latestPrices->first();
            $priceYesterdayData = $latestPrices->count() > 1 ? $latestPrices->last() : null;

            $avgPriceToday = $priceTodayData->harga;
            $avgPriceYesterday = $priceYesterdayData ? $priceYesterdayData->harga : null;

            if ($avgPriceYesterday === null) {
                $changeStatus = 'no-previous-data';
                $percentageChange = 0;
            } else {
                $priceChange = $avgPriceToday - $avgPriceYesterday;
                $percentageChange = ($avgPriceYesterday > 0)
                    ? ($priceChange / $avgPriceYesterday) * 100
                    : 0;

                $changeStatus = 'same';
                if ($percentageChange > 0.01) {
                    $changeStatus = 'up';
                } elseif ($percentageChange < -0.01) {
                    $changeStatus = 'down';
                    $percentageChange = abs($percentageChange);
                }
            }
            $itemLatestDate = Carbon::parse($priceTodayData->tanggal)->translatedFormat('d M Y');

            $summary[] = [
                'id' => $bahanPokok->id,
                'name' => $bahanPokok->nama,
                'image_url' => $bahanPokok->foto,
                'unit' => $bahanPokok->satuan,
                'price' => round($avgPriceToday),
                'tanggal_terakhir' => $itemLatestDate,
                'change_status' => $changeStatus,
                'change_percent' => number_format($percentageChange, 2),
            ];
        }

        return response()->json($summary);
    }
}