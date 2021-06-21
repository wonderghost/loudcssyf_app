<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSupervisorTypeToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN `type` ENUM('admin','v_standart','v_da','commercial','logistique','gcga','grex','gdepot','controleur','coursier','technicien','pdc','pdraf','graf','client','g_compte','supervisor')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
