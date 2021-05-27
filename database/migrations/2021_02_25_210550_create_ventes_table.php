<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nom');
            $table->string('prenom');
            $table->string('quartier');
            $table->string('materiel');
            $table->string('formule');
            $table->string('option');
            $table->integer('duree');
            $table->string('telephone');
            $table->float('montant',8,0);
            $table->string('id_user');
            $table->enum('type',['recrutement','reabonnement','upgrade']);
            $table->timestamps();

            $table->foreign('formule')
                ->references('nom')
                ->on('formule');

            $table->foreign('id_user')
                ->references('username')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventes');
    }
}
