<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnStatusToAddAbortedActionPrim extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_material', function (Blueprint $table) {
            //
            $table->enum('status',['confirmed','unconfirmed','aborted'])->default('unconfirmed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('command_material', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
}
