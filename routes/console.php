<?php

// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    try {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        Log::info("Scheduler: Mencari data dari tanggal $yesterday.");

        $dataKemarin = DB::table('harga_bapok')
            ->whereDate('tanggal', $yesterday)
            ->get();

        Log::info("Scheduler: Ditemukan " . $dataKemarin->count() . " item untuk disalin.");

        if ($dataKemarin->isEmpty()) {
            Log::info("Tidak ada data harga_bapok yang ditemukan untuk disalin.");
            return;
        }

        foreach ($dataKemarin as $item) {
            DB::table('harga_bapok')->updateOrInsert(
                // Kriteria pencarian: Jika kombinasi ini ada, update. Jika tidak, buat baru.
                [
                    'id_pasar' => $item->id_pasar,
                    'id_bahan_pokok' => $item->id_bahan_pokok,
                    'tanggal' => $today,
                ],
                // Nilai yang akan dimasukkan atau diperbarui
                [
                    'harga' => $item->harga,
                    'stok' => $item->stok,
                    'status_integrasi' => 'pending',
                    'created_by' => 'Scheduler',
                    'created_at' => now(), // created_at akan diperbarui jika ada update, atau diset jika insert
                    'updated_at' => now(),
                ]
            );
        }

        Log::info("Data harga_bapok berhasil dicopy otomatis untuk tanggal $today.");

    } catch (\Exception $e) {
        Log::error("Scheduler Error: " . $e->getMessage());
    }
})->dailyAt('11:23'); // Pastikan waktu ini sesuai dengan kebutuhan Anda

Schedule::command('inspire')->everyMinute();