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
        Schema::create('histori_bahanbaku', function (Blueprint $table) {
            $table->integer('id_histori_bahanbaku', true);
            $table->integer('id_bahan_baku')->index('history_ibfk_1');
            $table->date('tanggal_pakai');
            $table->float('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histori_bahanbaku');
    }
};
