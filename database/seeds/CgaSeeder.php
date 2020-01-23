<?php

use Illuminate\Database\Seeder;

class CgaSeeder extends Seeder
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
            'designation' => 'cga',
            'solde' =>  0
        ]);
    }
}
