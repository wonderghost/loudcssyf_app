<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertRetraitAfrocash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('retrait_afrocashes', function (Blueprint $table) {
            //
            $table->dropColumn('confirm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('retrait_afrocashes', function (Blueprint $table) {
            //
            $table->boolean('confirm')->default(false);
        });
    }
}
