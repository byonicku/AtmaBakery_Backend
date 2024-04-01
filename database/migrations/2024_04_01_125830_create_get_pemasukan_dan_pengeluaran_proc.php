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
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `get_pemasukan_dan_pengeluaran`(IN `target_month` INT, IN `target_year` INT)
BEGIN
    SELECT nama, pemasukan, pengeluaran
    FROM (
        SELECT nama, pemasukan, pengeluaran
        FROM (
            SELECT 'Transaksi' AS nama, COALESCE(SUM(total), 0) AS pemasukan, NULL AS pengeluaran
            FROM transaksi
            WHERE 
                MONTH(TANGGAL_LUNAS) = target_month AND YEAR(TANGGAL_LUNAS) = target_year
            
            UNION ALL
            
            SELECT 'Tip' AS nama, COALESCE(SUM(tip), 0) AS pemasukan, NULL AS pengeluaran
            FROM transaksi
            WHERE 
                MONTH(TANGGAL_LUNAS) = target_month AND YEAR(TANGGAL_LUNAS) = target_year
            
            UNION ALL
            
            SELECT nama, NULL AS pemasukan, total AS pengeluaran
            FROM pengeluaran
            WHERE 
                MONTH(TANGGAL_PENGELUARAN) = target_month AND YEAR(TANGGAL_PENGELUARAN) = target_year
        ) AS tabel_transaksi
        
        UNION ALL
        
        SELECT 'Total' AS nama, SUM(pemasukan) AS pemasukan, SUM(pengeluaran) AS pengeluaran
        FROM (
            SELECT COALESCE(SUM(total), 0) AS pemasukan, NULL AS pengeluaran
            FROM transaksi
            WHERE 
                MONTH(TANGGAL_LUNAS) = target_month AND YEAR(TANGGAL_LUNAS) = target_year
            
            UNION ALL
            
            SELECT COALESCE(SUM(tip), 0) AS pemasukan, NULL AS pengeluaran
            FROM transaksi
            WHERE 
                MONTH(TANGGAL_LUNAS) = target_month AND YEAR(TANGGAL_LUNAS) = target_year
            
            UNION ALL
            
            SELECT NULL AS pemasukan, COALESCE(SUM(total), 0) AS pengeluaran
            FROM pengeluaran
            WHERE 
                MONTH(TANGGAL_PENGELUARAN) = target_month AND YEAR(TANGGAL_PENGELUARAN) = target_year
        ) AS tabel_pengeluaran
    ) AS laporan_pemasukan_pengeluaran;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_pemasukan_dan_pengeluaran");
    }
};
