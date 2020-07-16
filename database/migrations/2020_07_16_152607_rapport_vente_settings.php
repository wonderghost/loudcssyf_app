<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RapportVenteSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('commission_settings',function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('pourcentage_recrutement')->default(5.5);
            $table->float('pourcentage_reabonnement')->default(5.5);
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
        Schema::dropIfExists('commission_settings');
    }
}
