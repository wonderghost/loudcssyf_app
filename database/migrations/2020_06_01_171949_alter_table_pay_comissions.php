<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePayComissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('pay_comissions',function(Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('pay_comissions',function(Blueprint $table) {
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
        //
    }
}
