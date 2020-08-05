<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayComissionIdToReaboAfrocash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reabo_afrocash', function (Blueprint $table) {
            //
            $table->string('pay_comission_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reabo_afrocash', function (Blueprint $table) {
            //
            $table->dropColumn('pay_comission_id');
        });
    }
}
