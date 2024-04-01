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
        Schema::table('resep', function (Blueprint $table) {
            $table->foreign(['id_bahan_baku'], 'resep_ibfk_1')->references(['id_bahan_baku'])->on('bahan_baku')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_produk'], 'resep_ibfk_2')->references(['id_produk'])->on('produk')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resep', function (Blueprint $table) {
            $table->dropForeign('resep_ibfk_1');
            $table->dropForeign('resep_ibfk_2');
        });
    }
};
