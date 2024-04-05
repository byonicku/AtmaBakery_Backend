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
        DB::unprepared("CREATE FUNCTION `calculate_ongkir`(`no_nota_in` VARCHAR(20)) RETURNS float
        BEGIN
            DECLARE delivery_fee FLOAT;

            SELECT
                CASE
                    WHEN tipe_delivery = 'Ambil' THEN 0
                    WHEN radius <= 5 THEN 10000
                    WHEN radius > 5 AND radius <= 10 THEN 15000
                    WHEN radius > 10 AND radius <= 15 THEN 20000
                    ELSE 25000
                END INTO delivery_fee
            FROM transaksi
            WHERE no_nota = no_nota_in;

            return delivery_fee;
        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_ongkir");
    }
};
