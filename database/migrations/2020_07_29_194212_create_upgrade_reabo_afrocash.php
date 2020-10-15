<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpgradeReaboAfrocash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upgrade_reabo_afrocash', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from_formule');
            $table->string('to_formule');

            $table->foreign('from_formule')
                ->references('nom')
                ->on('formule');

            $table->foreign('to_formule')
                ->references('nom')
                ->on('formule');

            $table->string('id_reabo_afrocash');
            $table->foreign('id_reabo_afrocash')
                ->references('id')
                ->on('reabo_afrocash');
                
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
        Schema::dropIfExists('upgrade_reabo_afrocash');
    }
}
