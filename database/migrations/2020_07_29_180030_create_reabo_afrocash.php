<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReaboAfrocash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reabo_afrocash', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('serial_number');

            $table->string('formule_name');
            $table->foreign('formule_name')
                ->references('nom')
                ->on('formule');

            $table->UnsignedInteger('duree')->default(1);
            $table->string('telephone_client');
            $table->float('montant_ttc',8,0)->default(0);
            $table->float('comission',8,0)->default(0);

            $table->timestamp('pay_at')->nullable();
            $table->timestamp('remove_at')->nullable();
            $table->timestamp('confirm_at')->nullable();

            $table->string("pdraf_id");
            $table->foreign('pdraf_id')
                ->references('username')
                ->on('users');
            
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
        Schema::dropIfExists('reabo_afrocash');
    }
}
