<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockVendeur extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('stock_vendeur',function(Blueprint $table) {
            $table->string('produit');
            $table->string('vendeurs');
            $table->unsignedInteger('quantite');
            $table->timestamps();
            $table->primary(['produit','vendeurs']);
            $table->foreign('produit')->references('reference')->on('produits');
            $table->foreign('vendeurs')->references('username')->on('users');
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
        Schema::dropIfExists('stock_vendeur');
    }
}
