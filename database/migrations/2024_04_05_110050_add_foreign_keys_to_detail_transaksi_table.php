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
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->foreign(['id_hampers'], 'detail_transaksi_ibfk_1')->references(['id_hampers'])->on('hampers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_produk'], 'detail_transaksi_ibfk_2')->references(['id_produk'])->on('produk')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['no_nota'], 'detail_transaksi_ibfk_3')->references(['no_nota'])->on('transaksi')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropForeign('detail_transaksi_ibfk_1');
            $table->dropForeign('detail_transaksi_ibfk_2');
            $table->dropForeign('detail_transaksi_ibfk_3');
        });
    }
};
