<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultPropToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_afrocashes', function (Blueprint $table) {
            //
            if(Schema::hasColumn('transaction_afrocashes','compte_credite')) {
                $table->string('compte_credite')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_afrocashes', function (Blueprint $table) {
            //
            if(Schema::hasColumn('transaction_afrocashes','compte_credite')) {
                $table->string('compte_credite')->change();
            }
        });
    }
}
