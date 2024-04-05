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
            $table->string('email')->unique('email_unique');
            $table->integer('poin')->nullable()->default(0);
            $table->float('saldo')->nullable()->default(0);
            $table->string('password');
            $table->date('tanggal_lahir');
            $table->string('foto_profil')->nullable();
            $table->string('public_id')->nullable();
            $table->boolean('active')->default(false);
            $table->string('verify_key', 100);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
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
