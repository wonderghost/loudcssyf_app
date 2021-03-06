<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPromoIdToCommandMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('command_material', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('promos_id')->nullable();
            $table->foreign('promos_id')->references('id')->on('promos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('command_material', function (Blueprint $table) {
            //
            $table->dropForeign('command_material_promos_id_foreign');
            $table->dropColumn('promos_id');
        });
    }
}
