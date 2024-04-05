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
        DB::unprepared("CREATE PROCEDURE `update_bahan_baku_for_nota`(IN `nota` VARCHAR(20), IN `produk_id` INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE bahan_baku_id INT;
    DECLARE bahan_baku_quantity FLOAT;

    DECLARE cur CURSOR FOR
        SELECT r.id_bahan_baku, r.kuantitas * dt.jumlah AS total_quantity
        FROM resep r
        JOIN detail_transaksi dt ON r.id_produk = produk_id
        WHERE dt.no_nota = nota;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO bahan_baku_id, bahan_baku_quantity;
        IF done THEN
            LEAVE read_loop;
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
        DB::unprepared("DROP PROCEDURE IF EXISTS update_bahan_baku_for_nota");
    }
};
