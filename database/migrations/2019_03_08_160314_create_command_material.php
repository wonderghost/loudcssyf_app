<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandMaterial extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('command_material',function (Blueprint $table) {

          $table->increments('id');

          $table->string('id_commande')->unique();
          $table->enum('status',['confirmed','unconfirmed'])->default('unconfirmed');
          $table->string("vendeurs");
          $table->foreign('vendeurs')->references('username')->on('users');

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
        Schema::dropIfExists('command_material');
    }
}
