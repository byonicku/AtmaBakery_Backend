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
        Schema::create('pengadaan_bahanbaku', function (Blueprint $table) {
            $table->integer('id_pengadaan', true);
            $table->integer('id_bahan_baku')->index('id_bahan_baku');
            $table->date('tanggal_pembelian');
            $table->integer('stok');
            $table->float('harga');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengadaan_bahanbaku');
    }
};
