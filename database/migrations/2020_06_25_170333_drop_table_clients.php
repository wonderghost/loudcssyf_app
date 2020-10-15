<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('clients');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::create('clients',function(Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique()->default('undefined');
            $table->string('phone')->default('undefined');
            $table->string('adresse')->default('undefined');
            $table->string('nom');
            $table->string('prenom');
            $table->timestamps();
        });
    }
}
