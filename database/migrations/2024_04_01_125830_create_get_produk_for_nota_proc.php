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
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `get_produk_for_nota`(IN `no_nota_in` VARCHAR(20))
BEGIN
	    SELECT nama_produk AS nama, ukuran, jumlah * harga AS total_harga FROM produk p JOIN detail_transaksi dt ON p.id_produk = dt.id_produk WHERE no_nota = no_nota_in
        UNION ALL
        SELECT nama_hampers AS nama, NULL as ukuran, jumlah * harga AS total_harga FROM hampers h JOIN detail_transaksi dt ON h.id_hampers = dt.id_hampers WHERE no_nota = no_nota_in;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_produk_for_nota");
    }
};
