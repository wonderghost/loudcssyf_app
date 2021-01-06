<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterventionTechniciensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intervention_techniciens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("numero_materiel");
            $table->string('nom_client');
            $table->string('adresse');
            $table->string('telephone');
            $table->string('id_recrutement_afrocash')
                ->unique()
                ->nullable();
                
            $table->string('id_technicien');
            $table->string('id_vendeur')->nullable();
            $table->enum('type',['installation','depannage'])->default('installation');
            $table->text('description');
            $table->timestamps();

            $table->foreign('id_recrutement_afrocash')
                ->references('id')
                ->on('recrutement_afrocashes');

            $table->foreign('id_technicien')
                ->references('username')
                ->on('users');

            $table->foreign('id_vendeur')
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
        Schema::dropIfExists('intervention_techniciens');
    }
}
