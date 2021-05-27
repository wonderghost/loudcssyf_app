<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepotAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('depot_afrocashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('expediteur');
            $table->string('destinateur');
            $table->float('montant',8,0);
            $table->timestamp('pdc_com_pay_at')->nullable();
            $table->timestamp('pdraf_com_pay_at')->nullable();
            $table->timestamp('central_com_pay_at')->nullable();
            $table->unsignedBigInteger('id_frais');

            $table->foreign('expediteur')
                ->references('numero_compte')
                ->on('afrocashes');

            $table->foreign('destinateur')
                ->references('numero_compte')
                ->on('afrocashes');

            $table->foreign('id_frais')
                ->references('id')
                ->on('comission_setting_afrocashes');

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
        Schema::dropIfExists('depot_afrocashes');
    }
}
