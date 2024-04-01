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
        DB::unprepared("CREATE DEFINER=`root`@`localhost` PROCEDURE `get_nota_pemesanan`(IN `no_nota_in` VARCHAR(20))
BEGIN
    SELECT nama, data_pemesanan FROM (
        SELECT 'no_nota' AS nama, no_nota AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'tanggal_pesan' AS nama, tanggal_pesan AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'tanggal_lunas' AS nama, tanggal_lunas AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'tanggal_ambil' AS nama, tanggal_ambil AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'nama_user' AS nama, nama AS data_pemesanan FROM user u JOIN transaksi t ON t.id_user = u.id_user WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'email' AS nama, email AS data_pemesanan FROM user u JOIN transaksi t ON t.id_user = u.id_user WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'lokasi' AS nama, lokasi AS data_pemesanan FROM alamat a JOIN transaksi t ON a.id_alamat = t.id_alamat WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'tipe_delivery' AS nama, tipe_delivery AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'penggunaan_poin' AS nama, COALESCE(penggunaan_poin, 0) AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'total' AS nama, total AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'radius' AS nama, radius AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'ongkir' AS nama, calculate_ongkir(no_nota_in) AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
        UNION ALL
        SELECT 'poin' AS nama, calculate_points(no_nota_in) AS data_pemesanan FROM transaksi WHERE no_nota = no_nota_in
    ) AS nota_pemesanan;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_nota_pemesanan");
    }
};
