<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\RexAccount;
use App\Formule;
use App\CommandCredit;
use App\Credit;
use App\Afrocash;
use App\Exceptions\AppException;
use Carbon\Carbon;
use App\RapportVente;

Trait Rapports {
	public function isExistRapportOnThisDate(Carbon $date,$vendeurs) {
	  $temp = RapportVente::where([
	    'date_rapport'  =>  $date->toDateTimeString(),
	    'vendeurs'  =>  $vendeurs
	    ])->first();
	  if($temp) {
	    return $temp;
	  }
	  return false;
	}
}
