<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ComissionAfrocashSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $comission = [
            [
                
                'from_amount'   =>  10000,
                'to_amount' =>  500000,
                'frais_pourcentage' =>  1,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  501000,
                'to_amount' =>  1000000,
                'frais_pourcentage' =>  0.9,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  1000001,
                'to_amount' =>  5000000,
                'frais_pourcentage' =>  0.8,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  5000001,
                'to_amount' =>  10000000,
                'frais_pourcentage' =>  0.7,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  10000001,
                'to_amount' =>  25000000,
                'frais_pourcentage' =>  0.65,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  25000001,
                'to_amount' =>  50000000,
                'frais_pourcentage' =>  0.6,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
        ];

        foreach($comission as $value) {
            DB::table('comission_setting_afrocashes')->insert($value);
        }
    }
}
