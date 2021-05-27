<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientInfoToRecrutementAfrocashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recrutement_afrocashes', function (Blueprint $table) {
            //
            $table->string('nom');
            $table->string('prenom');
            $table->string('ville');
            $table->string('adresse_postal')->nullable();
            $table->string('email')->nullable();
            $table->enum('titre',['Mr','Mme','Mlle'])->default('Mr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recrutement_afrocashes', function (Blueprint $table) {
            //
            $table->dropColumn(['nom','prenom','ville','adresse_postal','email','titre']);
        });
    }
}
