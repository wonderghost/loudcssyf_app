<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecrutementAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recrutement_afrocashes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('formule_name');
            $table->UnsignedInteger('duree')->default(1);
            $table->string('telephone_client');
            $table->float('montant_ttc')->default(0);
            $table->float('comission')->default(0);
            $table->string('pay_comission_id')->nullable();
            
            $table->timestamp('pay_at')->nullable();
            $table->timestamp('remove_at')->nullable();
            $table->timestamp('confirm_at')->nullable();

            $table->string('pdraf_id');

            $table->foreign('pdraf_id')
                ->references('username')
                ->on('users');

            $table->foreign('formule_name')
                ->references('nom')
                ->on('formule');

            $table->foreign('pay_comission_id')
                ->references('id')
                ->on('pay_comissions');

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
        Schema::dropIfExists('recrutement_afrocashes');
    }
}
