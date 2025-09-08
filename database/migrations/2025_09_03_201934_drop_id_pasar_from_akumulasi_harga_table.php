<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akumulasi_harga', function (Blueprint $table) {
            $table->dropForeign(['id_pasar']); // jika ada foreign key
            $table->dropColumn('id_pasar');    // drop kolomnya
        });
    }

    public function down(): void
    {
        Schema::table('akumulasi_harga', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pasar')->nullable();

            // tambahkan foreign key lagi kalau sebelumnya ada relasi
            $table->foreign('id_pasar')->references('id')->on('pasar')->onDelete('cascade');
        });
    }
};
