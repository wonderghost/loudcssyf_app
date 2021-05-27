<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComissionSettingToRetrait extends Migration
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
            $table->timestamp('pdc_com_pay_at')->nullable();
            $table->timestamp('pdraf_com_pay_at')->nullable();
            $table->timestamp('central_com_pay_at')->nullable();

            $table->unsignedBigInteger('id_frais')->nullable();

            $table->foreign('id_frais')
                ->references('id')
                ->on('comission_setting_afrocashes');
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
            $table->dropColumn(['pdc_com_pay_at','pdraf_com_pay_at','central_com_pay_at']);
            $table->dropForeign('retrait_afrocashes_id_frais_foreign');
        });
    }
}
