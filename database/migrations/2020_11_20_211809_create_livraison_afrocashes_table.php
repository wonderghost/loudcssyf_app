<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLivraisonAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livraison_afrocashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('command_afrocash');
            $table->boolean('state')->default(false);
            $table->string('confirm_code');
            $table->string('files')->nullable();
            
            $table->foreign('command_afrocash')
                ->references('id_commande')
                ->on('command_afrocashes');

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
        Schema::dropIfExists('livraison_afrocashes');
    }
}
