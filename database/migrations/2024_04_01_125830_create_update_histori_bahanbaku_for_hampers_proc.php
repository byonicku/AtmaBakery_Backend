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
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `update_histori_bahanbaku_for_hampers`(IN `nota` VARCHAR(20))
BEGIN
    DECLARE bahan_baku_id INT;
    DECLARE bahan_baku_quantity FLOAT;
    DECLARE done INT DEFAULT FALSE;
    
    DECLARE cur_resep CURSOR FOR 
        SELECT id_bahan_baku, total_quantity FROM temp_result;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    CREATE TEMPORARY TABLE temp_result AS
        SELECT r.id_bahan_baku, r.kuantitas * dh.jumlah AS total_quantity
        FROM detail_hampers dh
        JOIN detail_transaksi dt ON dh.id_hampers = dt.id_hampers
        JOIN resep r ON dh.id_produk = r.id_produk
        WHERE dt.no_nota = nota
        UNION
        SELECT dh.id_bahan_baku, dh.jumlah * dt.jumlah AS total_quantity
        FROM detail_hampers dh
        JOIN detail_transaksi dt ON dh.id_hampers = dt.id_hampers
        WHERE dh.id_bahan_baku IS NOT NULL AND dt.no_nota = nota;

    OPEN cur_resep;

    read_loop_resep:LOOP
        FETCH cur_resep INTO bahan_baku_id, bahan_baku_quantity;
        IF done THEN
            LEAVE read_loop_resep;
        END IF;

        INSERT INTO histori_bahanbaku (id_bahan_baku, tanggal_pakai, jumlah)
        VALUES (bahan_baku_id, NOW(), bahan_baku_quantity);
        
        UPDATE bahan_baku
        SET stok = stok - bahan_baku_quantity
        WHERE id_bahan_baku = bahan_baku_id;
    END LOOP;
	
    INSERT INTO histori_bahanbaku (id_bahan_baku, tanggal_pakai, jumlah)
    VALUES (27, NOW(), 1);

    UPDATE bahan_baku 
    SET stok = stok - 1
    WHERE id_bahan_baku = 27;
    
    CLOSE cur_resep;

    DROP TEMPORARY TABLE IF EXISTS temp_result;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS update_histori_bahanbaku_for_hampers");
    }
};
