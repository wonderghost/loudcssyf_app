<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfirmRemoveToRetraitAfrocash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('retrait_afrocashes', function (Blueprint $table) {
            //
            $table->timestamp('confirm_at')->nullable();
            $table->timestamp('remove_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('retrait_afrocashes', function (Blueprint $table) {
            //
            $table->dropColumn(['confirm_at','remove_at']);
        });
    }
}
