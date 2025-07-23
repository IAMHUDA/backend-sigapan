<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasar', function (Blueprint $table) {
            $table->dropColumn('id_api_pasar');
        });
    }

    public function down(): void
    {
        Schema::table('pasar', function (Blueprint $table) {
            $table->integer('id_api_pasar')->unique();
        });
    }
};
