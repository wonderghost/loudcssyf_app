<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepensesAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('depenses_afrocashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('motif',['paiement_salaire','loyers','connection_internet','carburant','credit_appel','commission'])->nullable();
            $table->text('description')->nullable();
            $table->float('montant',8,0);
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
        Schema::dropIfExists('depenses_afrocashes');
    }
}
