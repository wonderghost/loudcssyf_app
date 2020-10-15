<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewIdAbonnementToUpgrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upgrade', function (Blueprint $table) {
            //
            $table->string('old_abonnement')->nullable();

            $table->foreign('old_abonnement')
                ->references('id')
                ->on('abonnements')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upgrade', function (Blueprint $table) {
            $table->dropForeign('upgrade_old_abonnement_foreign');
            $table->dropColumn('old_abonnement');
        });
    }
}
