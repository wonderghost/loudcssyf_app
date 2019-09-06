<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLivraisonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ravitaillement');
            $table->string('produits');
            $table->unsignedInteger('quantite')->default(0);
            $table->string('depot');
            $table->enum('status',['livred','unlivred'])->default('unlivred');
            $table->string('code_livraison')->unique();

            $table->foreign('ravitaillement')->references('id_ravitaillement')->on('ravitaillement_vendeurs');
            $table->foreign('produits')->references('reference')->on('produits');

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
        Schema::dropIfExists('livraisons');
    }
}
