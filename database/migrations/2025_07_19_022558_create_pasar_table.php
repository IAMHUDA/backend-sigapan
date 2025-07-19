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
        Schema::create('pasar', function (Blueprint $table) {
            $table->id(); 
            $table->integer('id_api_pasar')->unique(); 
            $table->string('nama'); 
            $table->string('alamat')->nullable(); 
            $table->string('foto')->nullable(); 
            $table->integer('jumlah_pedagang')->nullable();
            $table->integer('jumlah_kios')->nullable(); 
            $table->integer('jumlah_mck')->nullable(); 
            $table->integer('jumlah_bango')->nullable(); 
            $table->integer('jumlah_kantor')->nullable(); 
            $table->string('tps')->nullable(); 
            $table->text('keterangan')->nullable(); 
            $table->decimal('latitude', 10, 7)->nullable(); 
            $table->decimal('longitude', 10, 7)->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasar');
    }
};

