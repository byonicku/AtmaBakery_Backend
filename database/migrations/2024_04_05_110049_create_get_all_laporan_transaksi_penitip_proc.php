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
        DB::unprepared("CREATE PROCEDURE `get_all_laporan_transaksi_penitip`(IN `month_param` INT, IN `year_param` INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE penitip_id VARCHAR(50);

    DECLARE cur CURSOR FOR
        SELECT DISTINCT id_penitip FROM produk;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO penitip_id;

        IF done THEN
            LEAVE read_loop;
        END IF;

        CALL get_laporan_transaksi_penitip(penitip_id, month_param, year_param);
    END LOOP;

    -- Close cursor
    CLOSE cur;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_all_laporan_transaksi_penitip");
    }
};
