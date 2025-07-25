<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_pokok', function (Blueprint $table) {
            $table->dropColumn('id_api_bahan_pokok');
        });
    }

    public function down(): void
    {
        Schema::table('bahan_pokok', function (Blueprint $table) {
            $table->integer('id_api_bahan_pokok');
        });
    }
};
