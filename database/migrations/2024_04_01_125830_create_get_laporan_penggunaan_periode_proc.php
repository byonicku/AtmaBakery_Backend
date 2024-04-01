<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `get_laporan_penggunaan_periode`(IN `start_date` DATE, IN `end_date` DATE)
BEGIN
    SELECT nama_bahan_baku, satuan, SUM(JUMLAH) as digunakan
    FROM bahan_baku b
    JOIN histori_bahanbaku h ON b.id_bahan_baku = h.id_bahan_baku
    WHERE TANGGAL_PAKAI >= start_date AND TANGGAL_PAKAI <= (end_date)
    GROUP BY nama_bahan_baku;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_laporan_penggunaan_periode");
    }
};
