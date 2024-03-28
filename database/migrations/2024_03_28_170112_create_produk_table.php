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
        Schema::create('produk', function (Blueprint $table) {
            $table->integer('id_produk', true);
            $table->string('id_kategori')->index('id_kategori');
            $table->string('nama_produk');
            $table->string('ukuran');
            $table->float('harga');
            $table->integer('limit');
            $table->string('id_penitip')->nullable()->index('id_penitip');
            $table->float('stok');
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
        Schema::dropIfExists('produk');
    }
};
