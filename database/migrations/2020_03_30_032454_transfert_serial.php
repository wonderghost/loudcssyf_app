<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransfertSerial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('transfert_serial',function(Blueprint $table) {
            $table->string('id_transfert');
            $table->string('serial');
            $table->primary(['id_transfert','serial']);

            $table->foreign('id_transfert')
                ->references('code')
                ->on('transfert_materiel');

            $table->foreign('serial')
                ->references('serial_number')
                ->on('exemplaire');
            
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
        //
        Schema::dropIfExists('transfert_serial');
    }
}
