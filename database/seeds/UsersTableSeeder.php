<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'username' => 'root',
            'email' => 'root@loudcssyf.com',
            'password' => bcrypt('loudcssyf'),
            'phone' =>  '+224624075702',
            'type'  =>  'admin',
            'agence'  =>  'AG-1234',
            'localisation'  =>  'aaaa'
        ]);
    }
}
