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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->string('no_nota')->primary();
            $table->integer('id_user')->index('id_user');
            $table->integer('id_alamat')->index('id_alamat');
            $table->dateTime('tanggal_pesan');
            $table->dateTime('tanggal_lunas')->nullable();
            $table->dateTime('tanggal_ambil')->nullable();
            $table->integer('penggunaan_poin')->nullable();
            $table->float('total');
            $table->float('radius');
            $table->float('tip');
            $table->string('tipe_delivery');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
};
