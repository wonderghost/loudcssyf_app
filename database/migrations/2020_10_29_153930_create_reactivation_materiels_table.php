<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReactivationMaterielsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reactivation_materiels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial_number');
            $table->timestamp('confirm_at')->nullable();
            $table->timestamp('remove_at')->nullable();
            
            $table->string('pdraf_id');

            $table->foreign('pdraf_id')
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
        Schema::dropIfExists('reactivation_materiels');
    }
}
