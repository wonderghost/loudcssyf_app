<?php

use Illuminate\Database\Seeder;

class AgenceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('agence')->insert([
            'reference' => 'AG-1234',
            'societe' => 'Loudcssyf-Sarl',
            'rccm' => "xxxx",
            'adresse' =>  'xxxx',
            'ville' =>  'xxxxx',
            'num_dist'  =>  'xxxx'
        ]);
    }
}
