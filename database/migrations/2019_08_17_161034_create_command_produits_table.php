<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandProduitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_produits', function (Blueprint $table) {
          $table->increments('id');
          $table->string('commande');
          $table->string('produit');
          $table->unsignedInteger('quantite_commande')->default(1);
          $table->unsignedInteger('parabole_a_livrer')->default(1);
          $table->foreign('commande')->references('id_commande')->on('command_material');
          $table->foreign('produit')->references('reference')->on('produits');
          $table->timestamps();
        });
        Schema::table('command_produits',function(Blueprint $table) {
            $table->boolean('status_ravitaillement')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('command_produits');
    }
}
