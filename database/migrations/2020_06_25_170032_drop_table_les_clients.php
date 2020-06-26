<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableLesClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('les_clients');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::create('les_clients',function(Blueprint $table) {
            $table->string('num')->primary();
            $table->string('email');
            $table->string('nom');
            $table->string('prenom');
            $table->string('phone');
            $table->string('adresse');
            $table->timestamps();
        });
    }
}
