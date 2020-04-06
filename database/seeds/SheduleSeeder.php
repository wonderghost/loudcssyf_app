<?php

use Illuminate\Database\Seeder;

class SheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        for($i = 0 ; $i < 10 ; $i++) {
            DB::table('schedule_test')->insert([
                'name'  =>  'JohnDoe'.$i,
                'surname'   =>  'Albert'.$i,
                'age'   =>  $i,
                'profession'    =>  'prof'.$i
            ]);
        }
    }
}
