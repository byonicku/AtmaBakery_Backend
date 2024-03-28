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
        Schema::table('detail_hampers', function (Blueprint $table) {
            $table->foreign(['id_hampers'], 'detail_hampers_ibfk_1')->references(['id_hampers'])->on('hampers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_produk'], 'detail_hampers_ibfk_2')->references(['id_produk'])->on('produk')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_bahan_baku'], 'detail_hampers_ibfk_3')->references(['id_bahan_baku'])->on('bahan_baku')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_hampers', function (Blueprint $table) {
            $table->dropForeign('detail_hampers_ibfk_1');
            $table->dropForeign('detail_hampers_ibfk_2');
            $table->dropForeign('detail_hampers_ibfk_3');
        });
    }
};
