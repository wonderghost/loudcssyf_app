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
            // [
                
            //     'from_amount'   =>  10000,
            //     'to_amount' =>  100000,
            //     'frais_pourcentage' =>  1000,
            //     'pdc_pourcentage'   =>  15,
            //     'pdraf_pourcentage' =>  30,
            //     'central_pourcentage'   =>  55
            // ],
            // [
                
            //     'from_amount'   =>  100001,
            //     'to_amount' =>  500000,
            //     'frais_pourcentage' =>  5000,
            //     'pdc_pourcentage'   =>  15,
            //     'pdraf_pourcentage' =>  30,
            //     'central_pourcentage'   =>  55
            // ],
            // [

            //     'from_amount'   =>  500001,
            //     'to_amount' =>  1000000,
            //     'frais_pourcentage' =>  9000,
            //     'pdc_pourcentage'   =>  15,
            //     'pdraf_pourcentage' =>  30,
            //     'central_pourcentage'   =>  55
            // ],
            // [

            //     'from_amount'   =>  1000001,
            //     'to_amount' =>  3000000,
            //     'frais_pourcentage' =>  24000,
            //     'pdc_pourcentage'   =>  15,
            //     'pdraf_pourcentage' =>  30,
            //     'central_pourcentage'   =>  55
            // ],
            // [

            //     'from_amount'   =>  3000001,
            //     'to_amount' =>  5000000,
            //     'frais_pourcentage' =>  40000,
            //     'pdc_pourcentage'   =>  15,
            //     'pdraf_pourcentage' =>  30,
            //     'central_pourcentage'   =>  55
            // ],
            // [

            //     'from_amount'   =>  5000001,
            //     'to_amount' =>  7000000,
            //     'frais_pourcentage' =>  49000,
            //     'pdc_pourcentage'   =>  15,
            //     'pdraf_pourcentage' =>  30,
            //     'central_pourcentage'   =>  55
            // ],
            [

                'from_amount'   =>  7000001,
                'to_amount' =>  9000000,
                'frais_pourcentage' =>  63000,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  9000001,
                'to_amount' =>  10000000,
                'frais_pourcentage' =>  70000,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  10000001,
                'to_amount' =>  15000000,
                'frais_pourcentage' =>  105000,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  15000001,
                'to_amount' =>  20000000,
                'frais_pourcentage' =>  130000,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  20000001,
                'to_amount' =>  25000000,
                'frais_pourcentage' =>  162500,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
            [

                'from_amount'   =>  25000001,
                'to_amount' =>  50000000,
                'frais_pourcentage' =>  300000,
                'pdc_pourcentage'   =>  15,
                'pdraf_pourcentage' =>  30,
                'central_pourcentage'   =>  55
            ],
        ];

        DB::table('comission_setting_afrocashes')
            ->where('id',1)
            ->update([
                'from_amount'   =>  10000,
                'to_amount' =>  100000,
                'frais_pourcentage' =>  1000
            ]);

        DB::table('comission_setting_afrocashes')
            ->where('id',2)
            ->update([
                'from_amount'   =>  100001,
                'to_amount' =>  500000,
                'frais_pourcentage' =>  5000
            ]);

            
        DB::table('comission_setting_afrocashes')
            ->where('id',3)
            ->update([
                'from_amount'   =>  500001,
                'to_amount' =>  1000000,
                'frais_pourcentage' =>  9000
            ]);
            
        DB::table('comission_setting_afrocashes')
            ->where('id',4)
            ->update([
                'from_amount'   =>  1000001,
                'to_amount' =>  3000000,
                'frais_pourcentage' =>  24000
            ]);
            
        DB::table('comission_setting_afrocashes')
            ->where('id',5)
            ->update([
                'from_amount'   =>  3000001,
                'to_amount' =>  5000000,
                'frais_pourcentage' =>  40000
            ]);
            
        DB::table('comission_setting_afrocashes')
            ->where('id',6)
            ->update([
                'from_amount'   =>  5000001,
                'to_amount' =>  7000000,
                'frais_pourcentage' =>  49000
            ]);



        foreach($comission as $value) {
            DB::table('comission_setting_afrocashes')->insert($value);
        }
    }
}
