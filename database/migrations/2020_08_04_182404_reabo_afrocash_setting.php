<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReaboAfrocashSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('reabo_afrocash_setting',function(Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('user_to_receive');
            $table->foreign('user_to_receive')
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
        Schema::dropIfExists('reabo_afrocash_setting');
    }
}
