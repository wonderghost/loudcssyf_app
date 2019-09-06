<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProduits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('produits',function(Blueprint $table) {
            $table->string('reference');
            $table->primary('reference');
            $table->string('libelle')->unique();
            $table->float('prix_achat')->default(0);
            $table->float('prix_vente')->default(0);
            $table->timestamps();
            $table->float('marge')->default(0);
            $table->float('prix_initial')->default(0);
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
        Schema::dropIfExists('produits');
    }
}
