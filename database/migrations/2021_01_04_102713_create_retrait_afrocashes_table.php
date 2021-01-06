<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRetraitAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retrait_afrocashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('montant',8,0);
            $table->boolean('confirm')->default(false);
            $table->string('initiateur');
            $table->string('destinateur');

            $table->foreign('initiateur')
                ->references('numero_compte')
                ->on('afrocashes');

            $table->foreign('destinateur')
                ->references('numero_compte')
                ->on('afrocashes');
            
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
        Schema::dropIfExists('retrait_afrocashes');
    }
}
