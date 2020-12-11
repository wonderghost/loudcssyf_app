<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecrutementAfrocashIdToTransactionAfrocashes extends Migration
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
            $table->string('recrutement_afrocash_id')->nullable();
            $table->foreign('recrutement_afrocash_id')
                ->references('id')
                ->on('recrutement_afrocashes');
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
            $table->dropForeign('transaction_afrocashes_recrutement_afrocash_id_foreign');
            $table->dropColumn('recrutement_afrocash_id');
        });
    }
}
