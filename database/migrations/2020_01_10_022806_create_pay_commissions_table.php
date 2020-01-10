<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('debut');
            $table->date('fin');
            $table->float('montant_total',8,0)->default(0);
            $table->enum('status',['validated','unvalidated'])->default('unvalidated');
            $table->string('vendeurs');
            $table->foreign('vendeurs')->references('username')->on('users');
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
      Schema::disableForeignKeyConstraints();
      Schema::dropIfExists('pay_commissions');
    }
}
