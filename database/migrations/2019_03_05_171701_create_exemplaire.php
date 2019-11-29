<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExemplaire extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('exemplaire',function (Blueprint $table) {
            $table->string('serial_number');
            $table->primary('serial_number');
            $table->string('produit');
            $table->string('vendeurs')->nullable();
            $table->enum('status',['actif','inactif'])->default('inactif');
            $table->timestamps();
            $table->foreign('produit')->references('reference')->on('produits');
            $table->foreign('vendeurs')->references('username')->on('users');
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
        Schema::dropIfExists('exemplaire');
    }
}
