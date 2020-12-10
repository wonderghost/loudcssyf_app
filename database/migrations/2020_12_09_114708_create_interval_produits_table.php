<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntervalProduitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interval_produits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('produit_id');
            $table->string('interval_id');

            $table->foreign('produit_id')
                ->references('reference')
                ->on('produits');

            $table->foreign('interval_id')
                ->references('id')
                ->on('intervals');

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
        Schema::table('interval_produits',function(Blueprint $table) {

            $table->dropForeign('interval_produits_produit_id_foreign');
            $table->dropForeign('interval_produits_interval_id_foreign');
            
        });

        Schema::dropIfExists('interval_produits');
    }
}
