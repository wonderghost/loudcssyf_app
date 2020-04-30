<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecouvrementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recouvrements', function (Blueprint $table) {
            $table->string('id');
            $table->primary('id');
            $table->float('montant',8,0)->default(0);
            $table->string('numero_recu')->unique();
            $table->string('vendeurs');
            $table->foreign('vendeurs')->references('username')->on('users');
            $table->timestamps();
        });

        Schema::table('transaction_afrocashes',function (Blueprint $table) {
          $table->string('recouvrement')->nullable();
          $table->foreign('recouvrement')->references('id')->on('recouvrements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recouvrements');
    }
}
