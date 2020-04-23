<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompenseMateriel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('compense',function (Blueprint $table) {
            $table->increments('id');
            $table->string('vendeurs');
            $table->string('materiel');
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->foreign('materiel')->references('reference')->on('produits');
            $table->unsignedInteger('quantite')->default(0);
            $table->enum('type',['debit','credit'])->default('debit');
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
        Schema::dropIfExists('compense');
    }
}
