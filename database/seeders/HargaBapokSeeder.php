<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HargaBapok;
use App\Models\AkumulasiHarga;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HargaBapokSeeder extends Seeder
{
    public function run()
    {
        $tanggal = Carbon::today()->toDateString();

        // 🔹 Hapus dulu data hari ini agar tidak bentrok unique key di HargaBapok
        HargaBapok::whereDate('tanggal', $tanggal)->delete();
        // 🔹 Hapus juga data akumulasi hari ini agar tidak ada duplikat atau data lama
        AkumulasiHarga::whereDate('tanggal', $tanggal)->delete();

        // daftar pasar & bahan pokok
        $pasarIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 15];
        $bahanPokokIds = [3, 5, 6, 9, 12, 13, 14, 15, 16, 18, 19, 21, 22, 23, 26];

        // 🔹 Insert data dummy harga hari ini
        foreach ($pasarIds as $pasarId) {
            foreach ($bahanPokokIds as $bahanId) {
                HargaBapok::create([
                    'id_pasar' => $pasarId,
                    'id_bahan_pokok' => $bahanId,
                    'tanggal' => $tanggal,
                    'harga' => rand(10000, 50000),
                    'created_by' => 'Seeder',
                    'stok' => rand(1, 100),
                    'status_integrasi' => 'approve',
                ]);
            }
        }

        // 🔹 Sekaligus jalankan logika akumulasi harga (seperti scheduler)
        try {
            Log::info('Seeder: Mulai menghitung rata-rata harga harian (per pasar, per bahan pokok).');

            $currentDate = Carbon::today()->toDateString();

            
            $results = HargaBapok::select(
                    'id_pasar', 
                    'id_bahan_pokok',
                    DB::raw('AVG(harga) as harga_rata2')
                )
                ->whereDate('tanggal', $currentDate) 
                ->groupBy('id_pasar', 'id_bahan_pokok') 
                ->get();

            $count = 0;
            foreach ($results as $row) {
                AkumulasiHarga::updateOrCreate(
                    [
                        'id_pasar' => $row->id_pasar, 
                        'id_bahan_pokok' => $row->id_bahan_pokok,
                        'tanggal' => $currentDate,
                    ],
                    [
                        'harga_rata2' => $row->harga_rata2,
                    ]
                );
                $count++;
            }
            Log::info("Seeder: Berhasil memperbarui $count data akumulasi harga untuk tanggal $currentDate.");
        } catch (\Exception $e) {
            Log::error("Seeder Akumulasi Error: " . $e->getMessage());
        }
    }
}
