<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbonnementOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonnement_option', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_abonnement');
            $table->string('id_option');
            $table->foreign('id_abonnement')
                ->references('id')
                ->on('abonnements');

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
        Schema::dropIfExists('abonnement_option');
    }
}
