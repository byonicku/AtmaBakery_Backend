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
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `get_laporan_transaksi_penitip`(IN `penitip_id` VARCHAR(50), IN `month_param` INT, IN `year_param` INT)
BEGIN
    SELECT 
        p.id_penitip AS \"ID Penitip\",
        nama AS \"Nama Penitip\",
        p.nama_produk AS \"Nama Produk\",
        COUNT(t.no_nota) AS \"Qty\",
        harga AS \"Harga Jual\",
        COUNT(t.no_nota) * harga AS \"Total\",
        COUNT(t.no_nota) * harga * 0.20 AS \"20% Komisi\",
        COUNT(t.no_nota) * harga * 0.80 AS \"Yang Diterima\"
    FROM transaksi t
    JOIN detail_transaksi dt ON t.no_nota = dt.no_nota
    JOIN produk p ON p.id_produk = dt.id_produk
    JOIN penitip pt ON pt.id_penitip = p.id_penitip
    WHERE 
        p.id_penitip = penitip_id 
        AND MONTH(t.tanggal_lunas) = month_param 
        AND YEAR(t.tanggal_lunas) = year_param
    GROUP BY 
    	p.id_penitip, nama, p.nama_produk, harga;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_laporan_transaksi_penitip");
    }
};
