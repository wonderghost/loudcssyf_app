<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidateToInterventionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intervention_techniciens', function (Blueprint $table) {
            //
            $table->boolean('validated')->default(false);
            $table->enum('status',['en_attente','effectif','avorter'])->default('en_attente');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intervention_techniciens', function (Blueprint $table) {
            //
            $table->dropColumn(['validated','status']);
        });
    }
}
