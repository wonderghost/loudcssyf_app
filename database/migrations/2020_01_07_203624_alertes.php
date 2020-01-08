<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Alertes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('alertes',function (Blueprint $table) {
            $table->string('notification');
            $table->string('vendeurs');
            $table->primary(['notification','vendeurs']);
            $table->enum('status',['read','unread'])->default('unread');
            $table->timestamps();
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->foreign('notification')->references('id')->on('notifications');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('alertes');
    }
}
