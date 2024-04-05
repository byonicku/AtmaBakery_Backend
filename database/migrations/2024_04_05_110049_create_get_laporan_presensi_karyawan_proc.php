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
        DB::unprepared("CREATE PROCEDURE `get_laporan_presensi_karyawan`(IN `target_date` DATE)
BEGIN
    SELECT nama, jumlah_hadir, jumlah_bolos, honor_harian, bonus, total
    FROM (
        SELECT
            k.nama,
            CASE
                WHEN jumlah_presensi IS NULL THEN DAY(LAST_DAY(target_date))
                ELSE DAY(LAST_DAY(target_date)) - jumlah_presensi
            END as jumlah_hadir,
            jumlah_presensi as jumlah_bolos,
            CASE
                WHEN jumlah_presensi IS NULL THEN DAY(LAST_DAY(target_date)) * gaji
                ELSE (DAY(LAST_DAY(target_date)) - jumlah_presensi) * gaji
            END as honor_harian,
            k.bonus,
            CASE
                WHEN jumlah_presensi IS NULL THEN DAY(LAST_DAY(target_date)) * gaji + k.bonus
                ELSE (DAY(LAST_DAY(target_date)) - jumlah_presensi) * gaji + k.bonus
            END as total
        FROM
            karyawan k
        LEFT JOIN (
            SELECT id_karyawan, COUNT(id_presensi) AS jumlah_presensi
            FROM presensi
            WHERE MONTH(tanggal) = MONTH(target_date) AND YEAR(tanggal) = YEAR(target_date)
            GROUP BY id_karyawan
        ) p ON k.id_karyawan = p.id_karyawan
    ) AS laporan_presensi

    UNION ALL

    SELECT
        'Total' as nama,
        NULL as jumlah_hadir,
        NULL as jumlah_bolos,
        NULL as honor_harian,
        NULL as bonus,
        SUM(total) as total
    FROM (
        SELECT
            k.nama,
            NULL as jumlah_hadir,
            NULL as jumlah_bolos,
            NULL as honor_harian,
            NULL as bonus,
            CASE
                WHEN jumlah_presensi IS NULL THEN DAY(LAST_DAY(target_date)) * gaji + k.bonus
                ELSE (DAY(LAST_DAY(target_date)) - jumlah_presensi) * gaji + k.bonus
            END as total
        FROM
            karyawan k
        LEFT JOIN (
            SELECT id_karyawan, COUNT(id_presensi) AS jumlah_presensi
            FROM presensi
            WHERE MONTH(tanggal) = MONTH(target_date) AND YEAR(tanggal) = YEAR(target_date)
            GROUP BY id_karyawan
        ) p ON k.id_karyawan = p.id_karyawan
    ) AS total_row;
END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS get_laporan_presensi_karyawan");
    }
};
