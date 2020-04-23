<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjVendeursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('obj_vendeurs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('plafond_recrutement');

            $table->float('plafond_reabonnement',8,0);

            $table->enum('classe_vendeur',['A','B','C']);

            $table->string('vendeurs');
            
            $table->string('id_objectif');

            $table->foreign('vendeurs')
                ->references('username')
                ->on('users');

            $table->foreign('id_objectif')
                ->references('id')
                ->on('objectifs');

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
        Schema::dropIfExists('obj_vendeurs');
    }
}
