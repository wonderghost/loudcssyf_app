<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionReaboAfrocash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('option_reabo_afrocash', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_reabo_afrocash');
            $table->foreign('id_reabo_afrocash')
                ->references('id')
                ->on('reabo_afrocash');

            $table->string('id_option');
            $table->foreign('id_option')
                ->references('nom')
                ->on('options');
                
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
        Schema::dropIfExists('option_reabo_afrocash');
    }
}
