<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionCredit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('transaction_credit',function(Blueprint $table) {
            $table->increments('code_transation');
            $table->enum('credits',['cga','rex'])->default('cga');
            $table->float('montant')->default(0);
            $table->timestamps();
            $table->foreign('credits')->references('designation')->on('credit');
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
        Schema::dropIfExists('transaction_credit');
    }
}
