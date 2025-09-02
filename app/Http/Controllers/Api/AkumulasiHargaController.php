<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkumulasiHarga;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\BahanPokok;

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



    public function getLaporan(Request $request, $id_bahan_pokok)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        // Ambil data bahan pokok
        $bahanPokok = BahanPokok::find($id_bahan_pokok);
        if (!$bahanPokok) {
            return response()->json(['message' => 'Bahan pokok tidak ditemukan'], 404);
        }

        // Query utama ambil data harga
        $query = AkumulasiHarga::with('pasar')
            ->where('id_bahan_pokok', $id_bahan_pokok);

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        $data = $query->orderBy('tanggal', 'asc')->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Tentukan start_date & end_date kalau tidak ada di query
        $startDate = $startDate ?? $data->min('tanggal');
        $endDate   = $endDate   ?? $data->max('tanggal');

        // Format tanggal dengan Carbon
        $startDateFormatted = Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y');
        $endDateFormatted   = Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y');

        // Hitung harga rata-rata per tanggal
        $hargaRata = $data->groupBy('tanggal')->map(function ($item, $tanggal) {
            return [
                'tanggal'    => Carbon::parse($tanggal)->locale('id')->translatedFormat('d M Y'),
                'harga_rata' => round($item->avg('harga_rata2'))
            ];
        })->values();

        // Data harga per pasar
        $hargaPerPasar = $data->map(function ($item) {
            return [
                'tanggal'     => Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d M Y'),
                'id_pasar'    => $item->pasar->id,
                'nama_pasar'  => $item->pasar->nama,
                'harga'       => $item->harga_rata2
            ];
        })->sortBy(['tanggal', 'id_pasar'])->values();

        // Harga ekstrem terbaru (ambil tanggal terakhir)
        $latestTanggal = $data->max('tanggal');
        $latestData = $data->where('tanggal', $latestTanggal);

        $hargaTertinggi = $latestData->sortByDesc('harga_rata2')->first();
        $hargaTerendah  = $latestData->sortBy('harga_rata2')->first();

        $hargaEkstrem = [
            'tanggal'              => Carbon::parse($latestTanggal)->locale('id')->translatedFormat('d M Y'),
            'harga_tertinggi'      => $hargaTertinggi->harga_rata2,
            'id_pasar_tertinggi'   => $hargaTertinggi->pasar->id,
            'nama_pasar_tertinggi' => $hargaTertinggi->pasar->nama,
            'harga_terendah'       => $hargaTerendah->harga_rata2,
            'id_pasar_terendah'    => $hargaTerendah->pasar->id,
            'nama_pasar_terendah'  => $hargaTerendah->pasar->nama,
        ];

        return response()->json([
            'id_bahan_pokok'        => (string) $id_bahan_pokok,
            'nama_bahan_pokok'      => $bahanPokok->nama,
            'start_date'            => $startDateFormatted,
            'end_date'              => $endDateFormatted,
            'harga_rata'            => $hargaRata,
            'harga_per_pasar'       => $hargaPerPasar,
            'harga_ekstrem_terbaru' => $hargaEkstrem
        ]);
    }
}
