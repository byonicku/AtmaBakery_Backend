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
        Schema::table('gambar', function (Blueprint $table) {
            $table->foreign(['id_produk'], 'gambar_ibfk_1')->references(['id_produk'])->on('produk')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['id_hampers'], 'gambar_ibfk_2')->references(['id_hampers'])->on('hampers')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gambar', function (Blueprint $table) {
            $table->dropForeign('gambar_ibfk_1');
            $table->dropForeign('gambar_ibfk_2');
        });
    }
};
