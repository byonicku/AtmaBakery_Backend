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
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign(['id_user'], 'transaksi_ibfk_1')->references(['id_user'])->on('user')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_alamat'], 'transaksi_ibfk_2')->references(['id_alamat'])->on('alamat')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign('transaksi_ibfk_1');
            $table->dropForeign('transaksi_ibfk_2');
        });
    }
};
