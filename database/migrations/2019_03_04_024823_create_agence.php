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
            $table->string('rccm')->default(NULL);
            $table->string('adresse')->default(NULL);
            $table->string('ville')->default('Conakry');
            $table->primary('reference');
            $table->timestamps();
            $table->string('num_dist')->default(NULL);
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
