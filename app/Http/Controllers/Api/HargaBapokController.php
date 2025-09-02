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
        // Mengambil tanggal dari permintaan, atau menggunakan tanggal hari ini sebagai default
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        // 1. Kueri utama hanya mengambil data untuk tanggal yang diminta
        $query = HargaBapok::with(['pasar', 'bahanPokok'])
            ->whereDate('tanggal', $tanggal) // <-- Tambahkan filter tanggal di sini
            ->orderBy('tanggal', 'desc');

        if ($request->has('id_pasar')) {
            $query->where('id_pasar', $request->id_pasar);
        }

        $hargaBapokCollection = $query->get();

        $resultsWithChanges = [];

        foreach ($hargaBapokCollection as $item) {
            $currentPrice = (int) $item->harga;

            // 2. Kueri untuk harga sebelumnya disesuaikan untuk mencari di tanggal sebelumnya saja
            $previousHarga = HargaBapok::where('id_pasar', $item->id_pasar)
                ->where('id_bahan_pokok', $item->id_bahan_pokok)
                ->whereDate('tanggal', '<', $tanggal) // Cari harga sebelum tanggal yang diminta
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->value('harga');

            $changeStatus = 'no-previous-data';
            $percentageChange = 0;

            if ($previousHarga !== null && $previousHarga > 0) {
                $selisih = $currentPrice - $previousHarga;
                $persen = ($selisih / $previousHarga) * 100;

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
            }
            $item->change_status = $changeStatus;
            $item->change_percent = number_format($percentageChange, 2);
            $resultsWithChanges[] = $item;
        }

        return response()->json($resultsWithChanges);
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

            // Hilangkan pengecekan existingData, supaya bisa insert meski sudah ada
            $validatedData['created_by'] = Auth::user()->name ?? 'system';

            $hargaBapok = HargaBapok::create($validatedData);

            // Cari harga sebelumnya untuk hitung persentase
            $hargaSebelumnya = HargaBapok::where('id_pasar', $validatedData['id_pasar'])
                ->where('id_bahan_pokok', $validatedData['id_bahan_pokok'])
                ->where('tanggal', '<', $validatedData['tanggal'])
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->value('harga');

            $perubahan = 'tidak ada data sebelumnya';
            $persen = null;

            if ($hargaSebelumnya !== null && $hargaSebelumnya > 0) {
                $selisih = $hargaBapok->harga - $hargaSebelumnya;
                $persen = ($selisih / $hargaSebelumnya) * 100;

                if ($persen > 0.1) {
                    $perubahan = 'naik ' . number_format($persen, 2) . '%';
                } elseif ($persen < -0.1) {
                    $perubahan = 'turun ' . number_format(abs($persen), 2) . '%';
                } else {
                    $perubahan = 'tetap';
                }
            }

            return response()->json([
                'message' => 'Data berhasil ditambahkan.',
                'data' => $hargaBapok,
                'perubahan' => $perubahan,
                'persentase' => $persen !== null ? round($persen, 2) . '%' : null
            ], 201);
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
                'stok' => 'required|integer|min:0',
                'status_integrasi' => 'nullable|string|max:255',
            ]);

            // cek duplikasi kombinasi pasar, bahan pokok, dan tanggal
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

            // simpan perubahan
            $harga_bapok->update($validatedData);

            $hargaSekarang = (int) $harga_bapok->harga;

            // ambil harga sebelumnya (tanggal lebih kecil)
            $hargaSebelumnya = HargaBapok::where('id_pasar', $harga_bapok->id_pasar)
                ->where('id_bahan_pokok', $harga_bapok->id_bahan_pokok)
                ->where('tanggal', '<', $harga_bapok->tanggal)
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->value('harga');

            $perubahan = 'tidak ada data sebelumnya';
            $persen = null;

            if ($hargaSebelumnya !== null && $hargaSebelumnya > 0) {
                $selisih = $hargaSekarang - $hargaSebelumnya;
                $persen = ($selisih / $hargaSebelumnya) * 100;

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


    public function table()
    {
        // Ambil semua harga per pasar dan bahan pokok, urutkan dari terbaru
        $data = HargaBapok::with(['pasar', 'bahanPokok'])
            ->whereNotNull('harga')
            ->where('harga', '>', 0)
            ->orderBy('id_bahan_pokok')
            ->orderBy('tanggal', 'asc') // supaya bisa bandingkan mundur
            ->get();

        $result = [];
        $lastHarga = []; // simpan harga terakhir tiap (id_pasar, id_bahan_pokok)

        foreach ($data as $item) {
            $stok = (int) $item->stok;
            $stokWajib = (int) optional($item->bahanPokok)->stok_wajib;
            $status = $stok >= $stokWajib ? 'Tersedia' : 'Terbatas';

            $hargaBaru = (int) $item->harga;
            $key = $item->id_pasar . '-' . $item->id_bahan_pokok;

            $changeStatus = 'N/A';
            $percentageChange = 0;

            if (isset($lastHarga[$key]) && $lastHarga[$key] > 0) {
                $hargaSebelumnya = $lastHarga[$key];
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
            }

            // update harga terakhir
            $lastHarga[$key] = $hargaBaru;

            $result[] = [
                'komoditas' => $item->bahanPokok ? ucwords($item->bahanPokok->nama) : null,
                'pasar' => $item->pasar ? $item->pasar->nama : null,
                'status' => $status,
                'stok' => $stok,
                'harga' => $hargaBaru,
                'tanggal' => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                'change_status' => $changeStatus,
                'change_percent' => number_format($percentageChange, 2),
            ];
        }

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
            $latestData = DB::table('harga_bapok')
                ->where('id_bahan_pokok', $bahanPokok->id)
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->orderBy('tanggal', 'desc')
                ->first();

            if (!$latestData) {
                continue;
            }
            $avgPriceToday = DB::table('harga_bapok')
                ->where('id_bahan_pokok', $bahanPokok->id)
                ->whereDate('tanggal', $latestData->tanggal)
                ->avg('harga');
            $previousDate = DB::table('harga_bapok')
                ->where('id_bahan_pokok', $bahanPokok->id)
                ->whereNotNull('harga')
                ->where('harga', '>', 0)
                ->whereDate('tanggal', '<', $latestData->tanggal)
                ->orderBy('tanggal', 'desc')
                ->value('tanggal');

            $avgPriceYesterday = null;
            if ($previousDate) {
                $avgPriceYesterday = DB::table('harga_bapok')
                    ->where('id_bahan_pokok', $bahanPokok->id)
                    ->whereDate('tanggal', $previousDate)
                    ->avg('harga');
            }

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

            $itemLatestDate = Carbon::parse($latestData->tanggal)->translatedFormat('d M Y');

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

    public function getHargaBahanPokok(Request $request, $id_bahan_pokok)
    {
        $startDate = $request->query('start_date', now()->subMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $idPasar = $request->query('id_pasar', null);

        $namaBahanPokok = HargaBapok::where('id_bahan_pokok', $id_bahan_pokok)
            ->first()
            ?->bahanPokok?->nama;

        $query = HargaBapok::query()
            ->select('harga_bapok.*', 'pasar.nama as nama_pasar')
            ->join('pasar', 'harga_bapok.id_pasar', '=', 'pasar.id')
            ->where('id_bahan_pokok', $id_bahan_pokok)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->when($idPasar, fn($q) => $q->where('harga_bapok.id_pasar', $idPasar))
            ->orderBy('tanggal');

        $allData = $query->get();

        if ($allData->isEmpty()) {
            return response()->json([
                'id_bahan_pokok' => $id_bahan_pokok,
                'nama_bahan_pokok' => $namaBahanPokok ?? 'Tidak Ditemukan',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'harga_rata' => [],
                'harga_per_pasar' => [],
                'harga_ekstrem_terbaru' => null,
            ]);
        }

        $hargaRata = $allData->groupBy('tanggal')->map(function ($items, $tanggal) {
            return [
                'tanggal' => $tanggal,
                'harga_rata' => (int) round($items->avg('harga')),
            ];
        })->values();

        $hargaPerPasar = $allData->map(function ($item) {
            return [
                'tanggal' => $item->tanggal,
                'id_pasar' => $item->id_pasar,
                'nama_pasar' => $item->nama_pasar,
                'harga' => (int) $item->harga,
            ];
        });

        $latestEntry = $allData->sortByDesc('tanggal')->first();
        $hargaEkstremTerbaru = null;

        if ($latestEntry) {
            $latestDate = $latestEntry->tanggal;
            $filteredByDate = $allData->where('tanggal', $latestDate);

            $hargaTertinggi = $filteredByDate->sortByDesc('harga')->first();
            $hargaTerendah = $filteredByDate->sortBy('harga')->first();

            if ($hargaTertinggi && $hargaTerendah) {
                $hargaEkstremTerbaru = [
                    'tanggal' => $latestDate,
                    'harga_tertinggi' => (int) $hargaTertinggi->harga,
                    'id_pasar_tertinggi' => $hargaTertinggi->id_pasar,
                    'nama_pasar_tertinggi' => $hargaTertinggi->nama_pasar,
                    'harga_terendah' => (int) $hargaTerendah->harga,
                    'id_pasar_terendah' => $hargaTerendah->id_pasar,
                    'nama_pasar_terendah' => $hargaTerendah->nama_pasar,
                ];
            }
        }

        return response()->json([
            'id_bahan_pokok' => $id_bahan_pokok,
            'nama_bahan_pokok' => $namaBahanPokok ?? 'Tidak Ditemukan',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'harga_rata' => $hargaRata,
            'harga_per_pasar' => $hargaPerPasar,
            'harga_ekstrem_terbaru' => $hargaEkstremTerbaru,
        ]);
    }

    public function destroy(HargaBapok $harga_bapok)
    {
        try {
            $harga_bapok->delete();

            return response()->json([
                'message' => 'Data berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}