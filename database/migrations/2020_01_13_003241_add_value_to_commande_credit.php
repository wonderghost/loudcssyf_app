<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValueToCommandeCredit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_credits', function (Blueprint $table) {
            //
            $table->enum('status',['unvalidated','validated','aborted'])->default('unvalidated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('command_credits', function (Blueprint $table) {
            //

        });
    }
}
