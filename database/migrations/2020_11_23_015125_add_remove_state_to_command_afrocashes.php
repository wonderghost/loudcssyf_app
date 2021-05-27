<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemoveStateToCommandAfrocashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_afrocashes', function (Blueprint $table) {
            //
            $table->boolean('remove_state')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('command_afrocashes', function (Blueprint $table) {
            //
            $table->dropColumn('remove_state');
        });
    }
}
