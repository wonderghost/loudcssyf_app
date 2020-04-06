<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateToRapport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapport_vente', function (Blueprint $table) {
            //
            $table->enum('state',['aborted','unaborted'])->default('unaborted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rapport_vente', function (Blueprint $table) {
            //
            $table->dropColumn('state');
        });
    }
}
