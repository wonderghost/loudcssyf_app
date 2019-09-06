<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRapportVente extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('rapport_vente',function (Blueprint $table) {
            $table->string('id_rapport',255);
            $table->primary('id_rapport');
            $table->string('vendeurs');
            $table->unsignedInteger('quantite_recrutement')->default(0);
            $table->unsignedInteger('quantite_migration')->default(0);
            $table->float('ttc_recrutement')->default(0);
            $table->float('ttc_reabonnement')->default(0);
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->date('date_rapport')->unique();
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
        Schema::dropIfExists('rapport_vente');
    }
}
