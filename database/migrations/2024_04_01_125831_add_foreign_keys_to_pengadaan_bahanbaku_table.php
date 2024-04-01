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
        Schema::table('pengadaan_bahanbaku', function (Blueprint $table) {
            $table->foreign(['id_bahan_baku'], 'pengadaan_bahanbaku_ibfk_1')->references(['id_bahan_baku'])->on('bahan_baku')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengadaan_bahanbaku', function (Blueprint $table) {
            $table->dropForeign('pengadaan_bahanbaku_ibfk_1');
        });
    }
};
