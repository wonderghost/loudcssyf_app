<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_afrocashes', function (Blueprint $table) {
            $table->bigIncrements('code_transaction');
            $table->string('compte_debite');
            $table->string('compte_credite');
            $table->foreign('compte_debite')->references('numero_compte')->on('afrocashes');
            $table->foreign('compte_credite')->references('numero_compte')->on('afrocashes');
            $table->float('montant',8,0)->default(0);
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
        Schema::dropIfExists('transaction_afrocashes');
    }
}
