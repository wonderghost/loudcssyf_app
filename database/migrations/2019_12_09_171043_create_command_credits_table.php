<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_credits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('montant',8,0)->default(0);
            $table->enum('status',['unvalidated','validated'])->default("unvalidated");
            $table->enum('type',['cga','rex','afro_cash_sg'])->default('cga');
            $table->string('vendeurs');
            $table->foreign('vendeurs')->references('username')->on("users");
            $table->string("numero_recu")->nullable();
            $table->string('recu')->nullable();
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
        Schema::dropIfExists('command_credits');
    }
}
