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
        Schema::table('presensi', function (Blueprint $table) {
            $table->foreign(['id_karyawan'], 'presensi_ibfk_1')->references(['id_karyawan'])->on('karyawan')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropForeign('presensi_ibfk_1');
        });
    }
};
