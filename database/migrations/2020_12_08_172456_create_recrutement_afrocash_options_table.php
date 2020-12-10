<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecrutementAfrocashOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recrutement_afrocash_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->string('id_recrutement_afrocash');
            $table->foreign('id_recrutement_afrocash')
                ->references('id')
                ->on('recrutement_afrocashes');

            $table->string('id_option');
            $table->foreign('id_option')
                ->references('nom')
                ->on('options');

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
        Schema::dropIfExists('recrutement_afrocash_options');
    }
}
