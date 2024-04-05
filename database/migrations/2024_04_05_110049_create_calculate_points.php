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
        DB::unprepared("CREATE FUNCTION `calculate_points`(`no_nota_in` VARCHAR(20)) RETURNS int(11)
        BEGIN
            DECLARE total_amount FLOAT;
            DECLARE points INT;
            DECLARE ongkir_in FLOAT;
            DECLARE user_birthday DATE;
            DECLARE start_date DATE;
            DECLARE end_date DATE;
            DECLARE now_date DATE;
            SET points = 0;

            SELECT t.total, u.tanggal_lahir, t.tanggal_pesan INTO total_amount, user_birthday, start_date
            FROM transaksi t
            JOIN user u ON t.id_user = u.id_user
            WHERE no_nota = no_nota_in;

            SET end_date = DATE_ADD(start_date, INTERVAL 3 DAY);
            SET start_date = DATE_ADD(start_date, INTERVAL -3 DAY);

            IF total_amount IS NULL THEN
                RETURN NULL;
            END IF;

             SELECT calculate_ongkir(no_nota_in) INTO ongkir_in;

            SET total_amount = total_amount - ongkir_in;

            count_point: LOOP
                IF ongkir_in = NULL THEN
                    LEAVE count_point;
                END IF;

                IF total_amount < 10000 THEN
                    LEAVE count_point;
                END IF;

                IF total_amount >= 1000000 THEN
                    SET points = points + 200;
                    SET total_amount = total_amount - 1000000;
                ELSEIF total_amount >= 500000 THEN
                    SET points = points + 75;
                    SET total_amount = total_amount - 500000;
                ELSEIF total_amount >= 100000 THEN
                    SET points = points + 15;
                    SET total_amount = total_amount - 100000;
                ELSEIF total_amount >= 10000 THEN
                    SET points = points + 1;
                    SET total_amount = total_amount - 10000;
                END IF;
            END LOOP count_point;

            IF DATE_FORMAT(user_birthday, '%m-%d') BETWEEN DATE_FORMAT(start_date, '%m-%d') AND DATE_FORMAT(end_date, '%m-%d') THEN
                SET points = points * 2;
            END IF;

            return points;
        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS calculate_points");
    }
};
