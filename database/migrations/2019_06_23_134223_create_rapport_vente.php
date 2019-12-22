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
            $table->string('vendeurs')->nullable();
            $table->unsignedInteger('quantite')->default(0);
            $table->float('montant_ttc',8,0)->default(0);
            $table->float('commission',8,0)->default(0);
            $table->enum('type',['recrutement','reabonnement','migration'])->default('reabonnement');
            $table->enum('credit_utilise',['cga','rex'])->nullable();
            $table->boolean('promo')->default(false);
            $table->string('id_rapport_promo')->nullable();
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->date('date_rapport')->unique();
            $table->timestamps();
        });

        Schema::table('exemplaire',function (Blueprint $table) {
          $table->foreign('rapports')->references('id_rapport')->on('rapport_vente');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('rapport_vente');
    }
}
