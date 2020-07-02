<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Formule;
use App\Exemplaire;
use Carbon\Carbon;
use App\Lesclients;
use App\Recrutements;
use App\CgaAccount;
use App\Abonnement;
use App\Http\Requests\SearchRequest;


Trait Abonnements {
	
	public function getAlertAbonnementForAllUsers(Abonnement $a) {
		try {
			$now = Carbon::now();
			$abonnements = $a->select()->orderBy('created_at','desc')->limit(2000)->get();

			$thisMonthAbonnementEnd = [];
			$thisMonthAbonnementInactif = [];
			$i=0;
			$j =0;
			foreach($abonnements as $key => $value) {
				$fin = new Carbon($value->debut);
				$fin->addMonths($value->duree)
					->subDay()
					->addHours(23)
					->addMinutes(59)
					->addSeconds(59);
				
				$diff = $now->diffInDays($fin,false);
				if($diff <= 10 && $diff > 0) {
					
					$thisMonthAbonnementEnd[$i] = $value;
					$i++;
				}

				if($diff < 0) {
					$thisMonthAbonnementInactif [$j] = $value;
					$j++;
				}
			}

			$data =[];
			$data_inactif = [];
			foreach($thisMonthAbonnementEnd as $key => $value) {
				$fin = new Carbon($value->debut);
				$fin->addMonths($value->duree)
					->subDay()
					->addHours(23)
					->addMinutes(59)
					->addSeconds(59);

				$debut = new Carbon($value->debut);
				$_diff = $now->diffInDays($fin,false);

				$data[$key] = [
					'serial'	=>	$value->serial_number,
					'distributeur'	=>	$value->rapportVente()->vendeurs()->localisation,
					'debut'	=>	$debut->toDateTimeString(),
					'fin'	=>	$fin->toDateTimeString(),
					'jours'	=>	$_diff
				];
			}
			
			foreach($thisMonthAbonnementInactif as $key => $value) {
				$fin = new Carbon($value->debut);
				$fin->addMonths($value->duree)
					->subDay()
					->addHours(23)
					->addMinutes(59)
					->addSeconds(59);

				$_diff = $now->diffInDays($fin,false);
				$data_inactif[$key] = [
					'serial'	=>	$value->serial_number,
					'distributeur'	=>	$value->rapportVente()->vendeurs()->localisation,
					'debut'	=>	$value->debut,
					'fin'	=>	$fin->toDateTimeString(),
					'jours'	=>	$_diff
				];
			}



			return response()
				->json([
					'relance'	=>	$data,
					'inactif'	=>	$data_inactif
					]);

		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

}