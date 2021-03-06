<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->call(AgenceTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(AfrocashSeeder::class);
        $this->call(CgaSeeder::class);
        $this->call(RexSeeder::class);
    }
}
