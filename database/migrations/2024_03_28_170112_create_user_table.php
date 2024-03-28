<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->integer('id_user', true);
            $table->string('id_role')->index('id_role');
            $table->string('nama');
            $table->string('no_telp', 13);
            $table->string('email')->unique();
            $table->integer('poin')->nullable();
            $table->float('saldo')->nullable();
            $table->string('password');
            $table->date('tanggal_lahir');
            $table->rememberToken();
            $table->timestamps();
            $table->timestamp('email_verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
};
