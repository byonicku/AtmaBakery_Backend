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
        Schema::table('histori_saldo', function (Blueprint $table) {
            $table->foreign(['id_user'], 'histori_saldo_ibfk_1')->references(['id_user'])->on('user')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('histori_saldo', function (Blueprint $table) {
            $table->dropForeign('histori_saldo_ibfk_1');
        });
    }
};
