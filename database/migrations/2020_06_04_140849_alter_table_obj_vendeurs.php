<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableObjVendeurs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('obj_vendeurs',function (Blueprint $table) {
            $table->timestamp('bonus_pay_at')->nullable();
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
        Schema::table('obj_vendeurs',function(Blueprint $table) {
            $table->dropColumn('bonus_pay_at');
        });
    }
}
