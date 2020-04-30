<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Remboursementpromo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('remboursement_promo',function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('montant',8,0);
            $table->integer('kits');
            $table->string('vendeurs');
            $table->unsignedBigInteger('promo_id');

            $table->foreign('vendeurs')
                ->references('username')
                ->on('users');
                
            $table->foreign('promo_id')
                ->references('id')
                ->on('promos');
            
            $table->timestamp('pay_at')->nullable();
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
        Schema::dropIfExists('remboursement_promo');
    }
}
