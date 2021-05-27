<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignkeyPromoidToRapport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapport_vente', function (Blueprint $table) {
            //
            $table->foreign('promo_id')
                ->references('id')
                ->on('promos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rapport_vente', function (Blueprint $table) {
            //
            $table->dropForeign('rapport_vente_promo_id_foreign');
        });
    }
}
