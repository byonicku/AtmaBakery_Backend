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
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `get_laporan_bulanan_keseluruhan`(IN `target_year` INT)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE found BOOLEAN DEFAULT FALSE;
    
    DROP TEMPORARY TABLE IF EXISTS temp_monthly_report;
    
    CREATE TEMPORARY TABLE temp_monthly_report (
        bulan VARCHAR(20),
        total_transaksi INT DEFAULT 0,
        total_pendapatan DECIMAL(10, 2) DEFAULT NULL
    );
    
    WHILE i <= 12 DO
        INSERT INTO temp_monthly_report (bulan, total_transaksi, total_pendapatan)
        SELECT 
            CASE 
                        WHEN i = 1 THEN 'Januari'
                        WHEN i = 2 THEN 'Februari'
                        WHEN i = 3 THEN 'Maret'
                        WHEN i = 4 THEN 'April'
                        WHEN i = 5 THEN 'Mei'
                        WHEN i = 6 THEN 'Juni'
                        WHEN i = 7 THEN 'Juli'
                        WHEN i = 8 THEN 'Agustus'
                        WHEN i = 9 THEN 'September'
                        WHEN i = 10 THEN 'Oktober'
                        WHEN i = 11 THEN 'November'
                        ELSE 'Desember'
            END AS bulan,
            COUNT(no_nota) as total_transaksi, 
            SUM(total) as total_pendapatan 
        FROM transaksi 
        WHERE YEAR(tanggal_lunas) = target_year AND MONTH(tanggal_lunas) = i;
        
        SET i = i + 1;
    END WHILE;
    
    INSERT INTO temp_monthly_report (bulan, total_pendapatan)
    SELECT 'Total', SUM(total_pendapatan) FROM temp_monthly_report;
    
    SELECT * FROM temp_monthly_report;
   
    DROP TEMPORARY TABLE IF EXISTS temp_monthly_report;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_laporan_bulanan_keseluruhan");
    }
};
