<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAfrocashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('afrocashes', function (Blueprint $table) {
            $table->string('numero_compte')->primary();
            $table->float('solde',8,0)->default(0);
            $table->enum('type',['courant','semi_grossiste'])->default('courant');
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
        Schema::dropIfExists('afrocashes');
    }
}
