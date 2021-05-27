<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableCommandAfrocashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('command_afrocashes',function(Blueprint $table) {
            $table->bigInteger('quantite');
            $table->bigInteger('quantite_a_livrer');
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
        Schema::table('command_afrocashes',function(Blueprint $table) {
            $table->dropColumn(['quantite','quantite_a_livrer']);
        });
    }
}
