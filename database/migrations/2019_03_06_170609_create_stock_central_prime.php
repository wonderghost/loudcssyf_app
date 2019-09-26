<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockCentralPrime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('stock_central_prime',function(Blueprint $table) {
            $table->string('produit');
            $table->string('depot');
            $table->primary(['produit','depot']);
            $table->unsignedInteger('quantite')->default(1);
            $table->foreign('produit')->references('reference')->on('produits');
            $table->foreign('depot')->references('localisation')->on('depots');
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
        Schema::dropIfExists('stock_central_prime');
    }
}
