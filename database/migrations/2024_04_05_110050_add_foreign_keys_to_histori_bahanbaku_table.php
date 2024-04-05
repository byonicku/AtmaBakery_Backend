<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('histori_bahanbaku', function (Blueprint $table) {
            $table->foreign(['id_bahan_baku'], 'history_ibfk_1')->references(['id_bahan_baku'])->on('bahan_baku')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('histori_bahanbaku', function (Blueprint $table) {
            $table->dropForeign('history_ibfk_1');
        });
    }
};
