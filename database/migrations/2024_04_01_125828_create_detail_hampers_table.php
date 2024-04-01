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
        Schema::create('detail_hampers', function (Blueprint $table) {
            $table->integer('id_detail_hampers', true);
            $table->integer('id_hampers')->nullable()->index('id_hampers');
            $table->integer('id_produk')->nullable()->index('id_produk');
            $table->integer('jumlah');
            $table->integer('id_bahan_baku')->nullable()->index('id_bahan_baku');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_hampers');
    }
};
