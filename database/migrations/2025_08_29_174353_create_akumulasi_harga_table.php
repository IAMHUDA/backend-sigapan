<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akumulasi_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pasar')->constrained('pasar')->onDelete('cascade');
            $table->foreignId('id_bahan_pokok')->constrained('bahan_pokok')->onDelete('cascade');
            
            $table->date('tanggal'); // tanggal akumulasi
            $table->decimal('harga_rata2', 12, 2)->nullable(); // rata-rata harga per tanggal
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akumulasi_harga');
    }
};
