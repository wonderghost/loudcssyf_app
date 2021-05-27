<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTransactionAfrocashes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if(!Schema::hasColumn('transaction_afrocashes','solde_anterieur')) {
            Schema::table('transaction_afrocashes',function(Blueprint $table) {
                $table->float('solde_anterieur',8,0)->nullable();
            });
        }

        if(!Schema::hasColumn('transaction_afrocashes','nouveau_solde')) {
            Schema::table('transaction_afrocashes',function(Blueprint $table) {
                $table->float('nouveau_solde',8,0)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('transaction_afrocashes',function(Blueprint $table) {
            // $table->dropColumn(['solde_anterieur','nouveau_solde']);
        });
    }
}
