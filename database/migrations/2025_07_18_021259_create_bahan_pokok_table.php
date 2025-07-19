<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('bahan_pokok', function (Blueprint $table) {
            $table->id(); // Secara otomatis membuat kolom 'id' UNSIGNED INT AUTO_INCREMENT PRIMARY KEY
            $table->integer('id_api_bahan_pokok');
            $table->integer('urutan');
            $table->string('nama');
            $table->string('satuan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps(); // Secara otomatis membuat created_at dan updated_at
            $table->integer('up_stok')->default(0);
            $table->integer('stok_wajib')->default(0);
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_pokok');
    }
};

