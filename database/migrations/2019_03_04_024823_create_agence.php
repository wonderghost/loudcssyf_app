<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgence extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('agence',function(Blueprint $table) {
            $table->string('reference');
            $table->string('societe')->default('Loudcssyf-sarl');
            $table->string('rccm')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->default('Conakry');
            $table->primary('reference');
            $table->timestamps();
            $table->string('num_dist')->nullable();
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
        Schema::dropIfExists('agence');
    }
}
