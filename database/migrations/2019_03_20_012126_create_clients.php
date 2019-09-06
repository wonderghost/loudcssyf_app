<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('clients',function(Blueprint $table) {
            $table->increments('id');
            // $table->string('code_client')->unique();
            $table->string('email')->unique()->default('undefined');
            $table->string('phone')->default('undefined');
            $table->string('adresse')->default('undefined');
            $table->string('nom');
            $table->string('prenom');
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
        //
        Schema::dropIfExists('clients');
    }
}
