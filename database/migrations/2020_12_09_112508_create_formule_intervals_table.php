<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormuleIntervalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formule_intervals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_interval');
            $table->string('id_formule');

            $table->foreign('id_interval')
                ->references('id')
                ->on('intervals');
            
            $table->foreign('id_formule')
                ->references('nom')
                ->on('formule');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formule_intervals',function(Blueprint $table) {
            $table->dropForeign('formule_intervals_id_interval_foreign');
            $table->dropForeign('formule_intervals_id_formule_foreign');

        });

        Schema::dropIfExists('formule_intervals');
    }
}
