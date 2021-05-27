<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteIntervalDataOnProduits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('produits',function(Blueprint $table){
            $table->dropColumn(['interval_serial_first','interval_serial_last']);
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
        Schema::table('produits',function(Blueprint $table) {
            $table->bigInteger('interval_serial_first')->nullable();
            $table->bigInteger('interval_serial_last')->nullable();
        });
    }
}
