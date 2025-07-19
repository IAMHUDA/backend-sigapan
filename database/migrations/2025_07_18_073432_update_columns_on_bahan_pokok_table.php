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
        Schema::table('bahan_pokok', function (Blueprint $table) {

            $table->string('nama', 50)->change();

            
            $table->string('satuan', 10)->nullable()->change();

        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('bahan_pokok', function (Blueprint $table) {
            
            $table->string('nama', 255)->change();

            
            $table->string('satuan', 255)->nullable()->change();
        });
    }
};

