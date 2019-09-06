<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompteCga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('compte_cga',function(Blueprint $table) {
            $table->string('numero')->primary();
            $table->float('solde')->default(0);
            $table->string('vendeur');
            $table->foreign('vendeur')->references('username')->on('users');
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
        Schema::dropIfExists('compte_cga');
    }
}
