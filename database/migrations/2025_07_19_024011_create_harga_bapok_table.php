<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('harga_bapok', function (Blueprint $table) {
            $table->id(); // Kolom ID utama (BIGINT UNSIGNED AUTO_INCREMENT)
            $table->foreignId('id_pasar')->constrained('pasar')->onDelete('cascade'); // Foreign key ke tabel 'pasars'
            $table->foreignId('id_bahan_pokok')->constrained('bahan_pokok')->onDelete('cascade'); // Foreign key ke tabel 'bahan_pokok'
            $table->date('tanggal'); // Tanggal harga dicatat
            $table->decimal('harga', 10, 0); // Harga bahan pokok (misal: 10000)
            $table->string('created_by', 200); // User yang membuat entri (misal: email atau nama user)
            $table->timestamps(); // Kolom created_at dan updated_at
            $table->string('stok', 6)->default('1'); // Kolom stok, default '1'
            $table->string('status_integrasi')->nullable(); // Status integrasi, bisa kosong

            // Menambahkan indeks unik untuk mencegah duplikasi harga pada tanggal, pasar, dan bahan pokok yang sama
            $table->unique(['id_pasar', 'id_bahan_pokok', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_bapok');
    }
};

