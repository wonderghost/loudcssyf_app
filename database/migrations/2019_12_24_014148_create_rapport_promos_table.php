<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRapportPromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rapport_promos', function (Blueprint $table) {
            $table->string('id',255);
            $table->primary('id');
            $table->unsignedBigInteger('quantite_a_compenser')->default(0);
            $table->float('compense_espece',8,0)->default(0);
            $table->enum('status_compense_espece',['paye','non_paye'])->default('non_paye');
            $table->unsignedBigInteger('promo');
            $table->timestamps();
        });

        Schema::table('rapport_vente',function (Blueprint $table) {
            $table->foreign('id_rapport_promo')->references('id')->on('rapport_promos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('rapport_promos');
    }
}
