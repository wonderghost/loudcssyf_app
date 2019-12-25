<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password',255);
            $table->string('phone')->nullable();
            $table->enum('type',['admin','v_standart','v_da','commercial','logistique','gcga','grex','gdepot','controleur'])->default('admin');
            $table->enum('status',['blocked','unblocked'])->default('unblocked');
            $table->rememberToken();
            $table->timestamps();
            $table->string('localisation')->nullable();
            $table->unique('localisation');

            $table->string('agence')->nullable();
            $table->foreign('agence')->references('reference')->on('agence');
            $table->string('rex')->nullable();
            $table->foreign('rex')->references('numero')->on('compte_rex');
        });

        Schema::disableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
