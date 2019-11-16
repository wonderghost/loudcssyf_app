<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLivraisonSerialFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livraison_serial_files', function (Blueprint $table) {
            $table->string('filename');
            $table->primary('filename');
            $table->unsignedInteger('livraison_id');
            $table->timestamps();
            $table->foreign('livraison_id')->references('id')->on('livraisons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('livraison_serial_files');
    }
}
