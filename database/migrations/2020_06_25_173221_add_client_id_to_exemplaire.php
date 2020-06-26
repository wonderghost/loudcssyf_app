<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientIdToExemplaire extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exemplaire', function (Blueprint $table) {
            //
            $table->string('client_id')->nullable();

            $table->foreign('client_id')
                ->references('client_slug')
                ->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exemplaire', function (Blueprint $table) {
            //
            $table->dropForeign('exemplaire_client_id_foreign');

            $table->dropColumn('client_id');
        });
    }
}
