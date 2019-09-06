<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionCga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('transaction_cga',function(Blueprint $table) {
            $table->increments('code_transaction');
            $table->string('cga');
            $table->float('montant')->default(0);
            $table->timestamps();
            $table->foreign('cga')->references('numero')->on('compte_cga');
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
        Schema::dropIfExists('transaction_cga');
    }
}
