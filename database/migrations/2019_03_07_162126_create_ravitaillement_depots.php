<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRavitaillementDepots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('ravitaillement_depots',function(Blueprint $table) {
            $table->increments('id');
            $table->string('produit');
            $table->string('depot');
            $table->string('origine')->nullable();
            $table->foreign('produit')->references('reference')->on('produits');
            $table->foreign('depot')->references('localisation')->on('depots');
            $table->foreign('origine')->references('localisation')->on('depots');
            $table->unsignedInteger('quantite')->default(1);
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
        Schema::dropIfExists('ravitaillement_depots');
    }
}
