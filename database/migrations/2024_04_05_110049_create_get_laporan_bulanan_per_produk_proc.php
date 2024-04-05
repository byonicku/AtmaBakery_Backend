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
        DB::unprepared("CREATE PROCEDURE `get_laporan_bulanan_per_produk`(IN `target_month` INT, IN `target_year` INT)
BEGIN
    SELECT nama_produk as nama, ukuran, SUM(jumlah) as kuantitas, harga, SUM(jumlah) * harga AS total_harga
    FROM produk p
    JOIN detail_transaksi dt ON dt.id_produk = p.id_produk
    JOIN transaksi t ON t.no_nota = dt.no_nota
    WHERE
        MONTH(TANGGAL_LUNAS) = target_month AND YEAR(TANGGAL_LUNAS) = target_year
    GROUP BY nama
    UNION
    SELECT nama_hampers as nama, NULL as ukuran, SUM(jumlah) as kuantitas, harga, SUM(jumlah) * harga AS total_harga
    FROM hampers h
    JOIN detail_transaksi dt ON dt.id_hampers = h.id_hampers
    JOIN transaksi t ON t.no_nota = dt.no_nota
    WHERE
        MONTH(TANGGAL_LUNAS) = target_month AND YEAR(TANGGAL_LUNAS) = target_year
    GROUP BY nama;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_laporan_bulanan_per_produk");
    }
};
