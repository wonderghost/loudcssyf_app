<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpgradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upgrade', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('depart');
            $table->string('finale');
            $table->string('id_abonnement');
            $table->string('id_options');

            $table->foreign('id_abonnement')
                ->references('id')
                ->on('abonnements');
            
            $table->foreign('depart')
                ->references('nom')
                ->on('formule');
                
            $table->foreign('finale')
                ->references('nom')
                ->on('formule');
            
            $table->foreign('id_options')
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
        Schema::dropIfExists('upgrade');
    }
}
