<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeblocageCga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deblocage_cga', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('user_account');
            $table->string('nom_prenom');
            $table->string('vendeurs');
            
            $table->foreign('vendeurs')
                ->references('username')
                ->on('users');

            $table->boolean('state_done')->default(false);
            
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
        Schema::dropIfExists('deblocage_cga');
    }
}
