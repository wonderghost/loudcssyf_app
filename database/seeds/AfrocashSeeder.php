<?php

use Illuminate\Database\Seeder;

class AfrocashSeeder extends Seeder
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
            'designation' => 'afrocash',
            'solde' =>  0
        ]);
    }
}
