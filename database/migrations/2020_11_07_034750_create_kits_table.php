<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_reference');
            $table->string('second_reference')->nullable();
            $table->string('third_reference')->nullable();
            $table->string('name');
            $table->text('description')->nullable();

            $table->foreign('first_reference')
                ->references('reference')
                ->on('produits');
                
            $table->foreign('second_reference')
                ->references('reference')
                ->on('produits');
                
            $table->foreign('third_reference')
                ->references('reference')
                ->on('produits');
                
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
        Schema::dropIfExists('kits');
    }
}
