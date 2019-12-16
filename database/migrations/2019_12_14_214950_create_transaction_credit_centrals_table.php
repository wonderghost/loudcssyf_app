<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionCreditCentralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_credit_centrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('expediteur',['cga','rex','afrocash'])->nullable();
            $table->enum('destinataire',['cga','rex','afrocash'])->nullable();
            $table->float('montant',8,0)->default(0);
            $table->enum('motif',['paiement_salaire','loyers','connection_internet','carburant','credit_appel','commission','autres'])->nullable();
            $table->enum('type',['depense','apport'])->default('depense');
            $table->text('description')->nullable();
            $table->foreign('expediteur')->references('designation')->on('credit');
            $table->foreign('destinataire')->references('designation')->on('credit');
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
        Schema::dropIfExists('transaction_credit_centrals');
    }
}
