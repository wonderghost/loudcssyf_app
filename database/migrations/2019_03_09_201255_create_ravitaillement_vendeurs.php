<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRavitaillementVendeurs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('ravitaillement_vendeurs',function(Blueprint $table) {
            $table->string('id_ravitaillement',255);
            $table->primary('id_ravitaillement');
            $table->string('vendeurs');
            $table->string('commands');
            $table->enum('livraison',['confirmer','non_confirmer'])->default('non_confirmer');
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->foreign('commands')->references('id_commande')->on('command_material');
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
        Schema::dropIfExists('ravitaillement_vendeurs');
    }
}
