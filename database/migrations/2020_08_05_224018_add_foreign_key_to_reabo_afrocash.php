<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToReaboAfrocash extends Migration
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
            $table->foreign('pay_comission_id')
                ->references('id')
                ->on('pay_comissions');
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
            $table->dropForeign('reabo_afrocash_pay_comission_id_foreign');
        });
    }
}
