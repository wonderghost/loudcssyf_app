<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbonnements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('rapport_id');
            $table->string('serial_number');
            $table->timestamp('debut');
            $table->UnsignedInteger('duree')->default(1);
            $table->string('formule_name');
            $table->boolean('upgrade')->default(false); 
            $table->timestamps();

            $table->foreign('rapport_id')
                ->references('id_rapport')
                ->on('rapport_vente');
            
            $table->foreign('serial_number')
                ->references('serial_number')
                ->on('exemplaire');

            $table->foreign('formule_name')
                ->references('nom')
                ->on('formule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abonnements');
    }
}
