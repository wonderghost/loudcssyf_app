<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableReseauxPdc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reseaux_pdc', function (Blueprint $table) {
            $table->string('id_pdc');
            $table->string('id_pdraf');
            $table->primary(['id_pdc','id_pdraf']);

            $table->foreign('id_pdc')
                ->references('username')
                ->on('users');
            
            $table->foreign('id_pdraf')
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
        Schema::dropIfExists('reseaux_pdc');
    }
}
