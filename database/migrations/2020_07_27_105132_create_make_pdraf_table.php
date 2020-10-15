<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMakePdrafTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('make_pdraf', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique()->nullable();
            $table->string('telephone')->unique();
            $table->string('agence')->unique();
            $table->string('adresse');
            $table->string('pdc_user_id');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->text('remove_notification')->nullable();
            // foreign key

            $table->foreign('pdc_user_id')
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
        Schema::dropIfExists('make_pdraf');
    }
}
