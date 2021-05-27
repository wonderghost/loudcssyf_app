<?php

use Illuminate\Database\Seeder;
use App\Formule;
use App\Option;
use Illuminate\Support\Str;

class formuleOptionSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $formules = Formule::all();
        $options = Option::all();

        foreach($formules as $value) {
            $value->title = $value->nom;
            // $value->nom = Str::slug($value->nom,'-');
            $value->update();
        }

        foreach($options as $value) {
            $value->title = $value->nom ;
            // $value->nom = Str::slug($value->nom,'-');
            $value->update();
        }
    }
}
