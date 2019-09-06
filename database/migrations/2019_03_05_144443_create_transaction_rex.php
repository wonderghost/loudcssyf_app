<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionRex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('transaction_rex',function(Blueprint $table) {
            $table->increments('code_transaction');
            $table->string('rex');
            $table->float('montant')->default(0);
            $table->timestamps();
            $table->foreign('rex')->references('numero')->on('compte_rex');

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
        Schema::dropIfExists('transaction_rex');
    }
}
