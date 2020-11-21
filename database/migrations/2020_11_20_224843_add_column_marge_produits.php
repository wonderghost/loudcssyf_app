<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMargeProduits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('produits',function(Blueprint $table) {
            $table->float('marge_pdc',8,0)->default(0);
            $table->float('marge_pdraf',8,0)->default(0);
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
            $table->dropColumn(['marge_pdc','marge_pdraf']);
        });
    }
}
