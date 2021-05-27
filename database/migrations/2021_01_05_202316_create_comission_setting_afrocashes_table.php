<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComissionSettingAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comission_setting_afrocashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('from_amount',8,0);
            $table->float('to_amount',8,0);
            $table->float('frais_pourcentage');
            $table->float('pdc_pourcentage');
            $table->float('pdraf_pourcentage');
            $table->float('central_pourcentage');
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
        Schema::dropIfExists('comission_setting_afrocashes');
    }
}
