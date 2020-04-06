<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransfertMateriel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('transfert_materiel',function(Blueprint $table) {
            $table->string('code');
            $table->primary('code');
            $table->string('expediteur');
            $table->string('destinataire');
            $table->unsignedInteger('quantite');
            $table->timestamps();

            $table->foreign('expediteur')
                ->references('username')
                ->on('users');

            $table->foreign('destinataire')
                ->references('username')
                ->on('users');

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
        Schema::dropIfExists('transfert_materiel');
    }
}
