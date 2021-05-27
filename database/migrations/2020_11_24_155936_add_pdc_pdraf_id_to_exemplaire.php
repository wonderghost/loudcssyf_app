<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPdcPdrafIdToExemplaire extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exemplaire', function (Blueprint $table) {
            //
            $table->string('pdc_id')->nullable();
            $table->string('pdraf_id')->nullable();

            $table->foreign('pdc_id')
                ->references('username')
                ->on('users');

            $table->foreign('pdraf_id')
                ->references('username')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exemplaire', function (Blueprint $table) {
            //
            $table->dropForeign('exemplaire_pdc_id_foreign');
            $table->dropForeign('exemplaire_pdraf_id_foreign');
            
            $table->dropColumn(['pdc_id','pdraf_id']);
        });
    }
}
