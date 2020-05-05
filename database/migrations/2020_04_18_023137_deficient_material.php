<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeficientMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('deficient_material',function(Blueprint $table) {
            $table->string('serial_to_replace');
            $table->string('serial_replacement');

            $table->text('motif')->nullable();

            $table->primary(['serial_to_replace','serial_replacement']);
            $table->string('vendeurs');

            $table->foreign('serial_to_replace')
                ->references('serial_number')
                ->on('exemplaire');
            
            $table->foreign('serial_replacement')
                ->references('serial_number')
                ->on("exemplaire");

            $table->foreign('vendeurs')
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
        //
        Schema::dropIfExists('deficient_material');
    }
}
