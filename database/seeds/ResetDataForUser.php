<?php

use Illuminate\Database\Seeder;
use App\Afrocash;
use App\CgaAccount;
use App\RexAccount;
use App\Credit;
class ResetDataForUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // REINITIALISATION DES COMPTE AFROCASH
      $afrocashAccount = Afrocash::all();
      $afrocashAccount->each(function ($element , $index) {
        $element->setSolde(0);
        $element->save();
        // CGA
        $cgaAccount = CgaAccount::all();
        $cgaAccount->each(function ($element,$index) {
          $element->setSolde(0);
          $element->save();
        });
        // REX
        $rexAccount = RexAccount::all();
        $rexAccount->each(function ($element,$index) {
          $element->setSolde(0);
          $element->save();
        });
        // compte central
        $compte = Credit::all();
        $compte->each(function ($element , $index) {
          $element->setSolde(0);
          $element->save();
        });
      });
    }
}
