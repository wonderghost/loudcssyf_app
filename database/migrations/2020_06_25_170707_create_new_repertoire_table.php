<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewRepertoireTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repertoire', function (Blueprint $table) {
            $table->string('vendeurs');
            $table->string('id_clients');

            $table->primary(['vendeurs','id_clients']);
            
            $table->foreign('vendeurs')
                ->references('username')
                ->on('users');
            
            $table->foreign('id_clients')
                ->references('client_slug')
                ->on('clients');

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
        Schema::dropIfExists('repertoire');
    }
}
