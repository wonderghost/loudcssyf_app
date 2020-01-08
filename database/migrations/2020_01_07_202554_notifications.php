<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Notifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('notifications',function (Blueprint $table) {
          $table->string('id')->primary();
          $table->string('titre');
          $table->text('description');
          $table->enum('status',['read','unread'])->default('unread');
          $table->string('vendeurs');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('notifications');
    }
}
