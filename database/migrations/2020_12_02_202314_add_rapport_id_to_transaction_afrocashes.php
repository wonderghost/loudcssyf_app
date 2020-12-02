<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRapportIdToTransactionAfrocashes extends Migration
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
            $table->string('rapport_id')
                ->nullable();

            $table->foreign('rapport_id')
                ->references('id_rapport')
                ->on('rapport_vente');
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
            $table->dropForeign('transaction_afrocashes_rapport_id_foreign');
            $table->dropColumn('rapport_id');
        });
    }
}
