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
        Schema::create('histori_saldo', function (Blueprint $table) {
            $table->dateTime('tanggal');
            $table->integer('id_user')->index('id_user');
            $table->float('saldo');
            $table->string('nama_bank');
            $table->string('no_rek');

            $table->primary(['tanggal', 'id_user']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histori_saldo');
    }
};
