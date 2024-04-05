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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->integer('id_detail_transaksi', true);
            $table->string('no_nota')->index('no_nota');
            $table->integer('id_produk')->nullable()->index('id_produk');
            $table->integer('id_hampers')->nullable()->index('detail_transaksi_ibfk_1');
            $table->integer('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
