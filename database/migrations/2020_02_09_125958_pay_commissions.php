<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayCommissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('pay_comissions',function( Blueprint $table ) {
          $table->string('id');
          $table->float('montant',8,0)->default(0);
          $table->enum('status',['unvalidated','validated'])->default('unvalidated');
          $table->timestamp('pay_at')->nullable();
          $table->timestamps();
          $table->primary('id');
        });

        Schema::table('rapport_vente',function(Blueprint $table) {
          $table->string('pay_comission_id')->nullable();
          $table->foreign('pay_comission_id')
            ->references('id')
            ->on('pay_comissions')
            ->onDelete('cascade');
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
        Schema::dropIfExists('pay_comissions');
    }
}
