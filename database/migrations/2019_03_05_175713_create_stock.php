<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('stock_central',function(Blueprint $table) {
            $table->string('exemplaire');
            $table->string('depot');
            $table->foreign('exemplaire')->references('serial_number')->on('exemplaire');
            $table->foreign('depot')->references('localisation')->on('depots');
            $table->unsignedInteger('quantite')->default(1);
            $table->string('origine')->default(null);
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
        Schema::dropIfExists('stock_central');
    }
}
