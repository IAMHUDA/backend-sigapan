<?php

// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\HargaBapok; // Pastikan model ini diimport
use App\Models\AkumulasiHarga; // Pastikan model ini diimport

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    try {
        $today = Carbon::today();
        
        // HANYA jalankan logika jika hari ini adalah Sabtu atau Minggu
        if ($today->isSaturday() || $today->isSunday()) {
            
            $sourceDate = $today->copy()->previous(Carbon::FRIDAY)->toDateString();
            $targetDate = $today->toDateString();

            Log::info("Scheduler: Mencari data dari tanggal $sourceDate untuk disalin ke tanggal $targetDate.");

            $dataJumat = DB::table('harga_bapok')
                ->whereDate('tanggal', $sourceDate)
                ->get();

            Log::info("Scheduler: Ditemukan " . $dataJumat->count() . " item untuk disalin.");

            if ($dataJumat->isEmpty()) {
                Log::info("Tidak ada data harga_bapok yang ditemukan untuk disalin dari hari Jumat.");
                return;
            }

            foreach ($dataJumat as $item) {
                DB::table('harga_bapok')->updateOrInsert(
                    // Kriteria pencarian
                    [
                        'id_pasar' => $item->id_pasar,
                        'id_bahan_pokok' => $item->id_bahan_pokok,
                        'tanggal' => $targetDate,
                    ],
                    // Nilai yang akan dimasukkan atau diperbarui
                    [
                        'harga' => $item->harga,
                        'stok' => $item->stok,
                        'status_integrasi' => $item->status_integrasi,
                        'created_by' => 'Scheduler',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            Log::info("Data harga_bapok berhasil dicopy otomatis untuk tanggal $targetDate dari data tanggal $sourceDate.");
            
        } else {
            // Log ini akan muncul jika hari ini bukan Sabtu atau Minggu
            Log::info("Scheduler: Tidak ada operasi penyalinan data yang dijadwalkan untuk hari ini.");
        }

    } catch (\Exception $e) {
        Log::error("Scheduler Error: " . $e->getMessage());
    }
})->dailyAt('09:35');

Schedule::command('inspire')->everyMinute();



Schedule::call(function () {
    try {
        Log::info('Scheduler: Mulai menghitung rata-rata harga harian (semua pasar per bahan pokok) untuk hari ini.');

        // Format tanggal pakai translatedFormat
        $currentDate = Carbon::today()->translatedFormat('d M Y');

        $results = HargaBapok::select(
                'id_bahan_pokok',
                DB::raw('AVG(harga) as harga_rata2')
            )
            ->whereDate('tanggal', Carbon::today()->toDateString()) // tetap filter pakai format DATE
            ->groupBy('id_bahan_pokok')
            ->get();

        $count = 0;
        foreach ($results as $row) {
            AkumulasiHarga::updateOrCreate(
                [
                    'id_bahan_pokok' => $row->id_bahan_pokok,
                    'tanggal' => $currentDate, // simpan tanggal hasil formatted
                ],
                [
                    'harga_rata2' => $row->harga_rata2,
                ]
            );
            $count++;
        }

        Log::info("Scheduler: Berhasil memperbarui $count data akumulasi harga untuk tanggal $currentDate.");
    } catch (\Exception $e) {
        Log::error("Scheduler Akumulasi Error: " . $e->getMessage());
    }
})->dailyAt('19:47');
