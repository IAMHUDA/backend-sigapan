<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('harga_bapok', function (Blueprint $table) {
        $table->dropColumn('harga_baru');
    });
}

public function down()
{
    Schema::table('harga_bapok', function (Blueprint $table) {
        $table->integer('harga_baru')->nullable();
    });
}

};
