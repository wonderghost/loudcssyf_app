<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableRepertoire extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('repertoire');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

        Schema::create('repertoire',function (Blueprint $table) {
            $table->increments('id');
            $table->string('client'); // email
            $table->string('vendeur'); // username
            $table->foreign('client')->references('email')->on('clients');
            $table->foreign('vendeur')->references('username')->on('users');
            $table->timestamps();
        });
    }
}
