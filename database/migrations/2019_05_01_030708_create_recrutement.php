<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecrutement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('recrutement',function(Blueprint $table) {
            $table->increments('id');
            $table->string('vendeurs');
            $table->string('clients');
            $table->string('serial_number');
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->foreign('clients')->references('num')->on('les_clients');
            $table->foreign('serial_number')->references('serial_number')->on('exemplaire');
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
        Schema::dropIfExists('recrutement');
    }
}
