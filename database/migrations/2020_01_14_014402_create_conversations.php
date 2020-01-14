<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_first');
            $table->string('user_second');
            $table->enum('state',['deleted','undeleted'])->default('undeleted');
            $table->foreign('user_first')->references('username')->on('users');
            $table->foreign('user_second')->references('username')->on('users');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('conversations');
    }
}
