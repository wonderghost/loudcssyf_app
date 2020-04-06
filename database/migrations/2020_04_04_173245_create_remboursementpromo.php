<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemboursementpromo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remboursementpromo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('montant',8,0)->default(0);
            $table->unsignedInteger('kit')->default(0);
            $table->string('vendeurs');
            $table->unsignedBigInteger('promo_id');

            $table->foreign('vendeurs')
                ->references('username')
                ->on('users');

            $table->foreign('promo_id')
                ->references('id')
                ->on('promos');

            $table->timestamp('pay_at');
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
        Schema::dropIfExists('remboursementpromo');
    }
}
