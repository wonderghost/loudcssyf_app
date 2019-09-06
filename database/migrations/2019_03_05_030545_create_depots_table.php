<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('depots', function (Blueprint $table) {
            $table->string('localisation')->primary();
            $table->timestamps();
        });

        Schema::table('depots',function (Blueprint $table) {
            $table->string('vendeurs');
            $table->foreign('vendeurs')->references('username')->on('users');
        });

        Schema::table('depots',function (Blueprint $table) {
            $table->unique('vendeurs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('depots');
    }
}
