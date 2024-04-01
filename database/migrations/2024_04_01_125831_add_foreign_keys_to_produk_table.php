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
        Schema::table('produk', function (Blueprint $table) {
            $table->foreign(['id_penitip'], 'produk_ibfk_2')->references(['id_penitip'])->on('penitip')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_kategori'], 'produk_ibfk_3')->references(['id_kategori'])->on('kategori')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropForeign('produk_ibfk_2');
            $table->dropForeign('produk_ibfk_3');
        });
    }
};
