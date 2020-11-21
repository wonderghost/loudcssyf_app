<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_afrocashes', function (Blueprint $table) {
            $table->string('id_commande');
            $table->string('user_id');
            $table->string('produit_id');
            $table->boolean('state')->default(false);

            $table->primary(['id_commande','user_id','produit_id']);
            
            
            $table->foreign('user_id')
                ->references('username')
                ->on('users');

            $table->foreign('produit_id')
                ->references('reference')
                ->on('produits');


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
        Schema::dropIfExists('command_afrocashes');
    }
}
