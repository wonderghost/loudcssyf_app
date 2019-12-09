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
            $table->float('prix_achat',8,0)->default(0);
            $table->float('prix_vente',8,0)->default(0);
            $table->boolean('with_serial')->default(true);
            $table->timestamps();
            $table->float('marge',8,0)->default(0);
            $table->float('prix_initial',8,0)->default(0);
        });
        Schema::table('produits', function( Blueprint $table) {
          $table->unsignedInteger('quantite_centrale')->default(0);
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
