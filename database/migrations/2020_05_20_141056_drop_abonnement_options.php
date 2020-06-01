<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAbonnementOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::disableForeignKeyConstraints();
        if(Schema::hasTable('abonnement_options')) {
            Schema::drop('abonnement_options');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

        // Schema::create('abonnement_options',function(Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('abonnement');
        //     $table->string('option');

        //     $table->foreign('abonnement')->references('numero')->on('abonnement');
        //     $table->foreign('option')->references('nom')->on('options');
        //     $table->timestamps();
        // });
    }
}
