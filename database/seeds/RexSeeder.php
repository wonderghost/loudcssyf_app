<?php

use Illuminate\Database\Seeder;

class RexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('credit')->insert([
            'designation' => 'rex',
            'solde' =>  0
        ]);
    }
}
