<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommandAfrocashIdToTransactionAfrocashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_afrocashes', function (Blueprint $table) {
            //
            $table->string('command_afrocash_id')->nullable();
            $table->foreign('command_afrocash_id')
                ->references('id_commande')
                ->on('command_afrocashes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_afrocashes', function (Blueprint $table) {
            //
            $table->dropForeign('transaction_afrocashes_command_afrocash_id_foreign');
            $table->dropColumn('command_afrocash_id');
        });
    }
}
