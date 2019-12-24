<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('intitule');
            $table->date('debut');
            $table->date('fin');
            $table->float('subvention',8,0)->default(0);
            $table->text('description')->nullable();
            $table->float('prix_vente',8,0)->default(0);
            $table->enum('status_promo',['actif','inactif'])->default('actif');
            $table->timestamps();
        });

        Schema::table('rapport_promos',function (Blueprint $table) {
            $table->foreign('promo')->references('id')->on('promos');
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
        Schema::dropIfExists('promos');
    }
}
