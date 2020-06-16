<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteColIdOptionOnUpgradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        
        Schema::table('upgrade',function (Blueprint $table) {
            $table->dropForeign('upgrade_id_options_foreign');
            $table->dropColumn('id_options');
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
        // Schema::table('upgrade',function(Blueprint $table) {
        //     $table->string('id_options');

        //     $table->foreign('id_options')
        //         ->references('nom')
        //         ->on('options');
        // });
    }
}
