<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCompense extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_compense', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('commande_id');
            $table->foreign('commande_id')
                ->references('id_commande')
                ->on('command_material');
            $table->unsignedInteger('quantite');
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
        Schema::dropIfExists('table_compense');
    }
}
