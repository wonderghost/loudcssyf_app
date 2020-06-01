<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAbonnementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::disableForeignKeyConstraints();
        if(Schema::hasTable('abonnement')) {
            Schema::drop('abonnement');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        // Schema::create('abonnement',function(Blueprint $table) {
        //     $table->string('numero')->primary();
        //     $table->string('formules');
        //     $table->string('vendeur');
        //     $table->string('client');
        //     $table->enum('status',['en_cours','en_attente','a_terme'])->default('en_cours');
        //     $table->dateTime('debut');
        //     $table->dateTime('fin');
        //     $table->string('materiel')->nullable();
        //     $table->foreign('materiel')->references('serial_number')->on('exemplaire');
        //     $table->foreign('formules')->references('nom')->on('formule');
        //     $table->foreign('vendeur')->references('username')->on('users');
        //     $table->timestamps();
        //     $table->foreign('client')->references('num')->on('les_clients');
        //     $table->enum('duree_abonnement',['1-mois','2-mois','3-mois','6-mois','9-mois','12-mois','24-mois'])->default('1-mois');

        // });
    }
}
