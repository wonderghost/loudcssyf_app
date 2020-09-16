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

use Illuminate\Support\Facades\DB;



Trait Abonnements {

	public function countAlertAbonnement(Abonnement $a) {
		try {
			$now = Carbon::now();

			return response()
				->json([
					'relance_count'	=>	count($this->filterAlertAbonnement($a,$now)['relance']),
					'inactif_count'	=>	count($this->filterAlertAbonnement($a,$now)['inactif'])
				]);

		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

	public function filterAlertAbonnement(Request $request,Abonnement $a , $now) {
		
		if($request->user()->type != 'v_da' && $request->user()->type != 'v_standart') {

			$serialGroup = DB::table('abonnements')
				->select('serial_number')
				->groupBy('serial_number')
				->paginate(100);
				
		}
		else {

			$rapportId = $request->user()
				->rapportVente()
				->select('id_rapport')
				->groupBy('id_rapport')
				->get();

			$serialGroup = Abonnement::select('serial_number')
				->whereIn('rapport_id',$rapportId)
				->groupBy('serial_number')
				->paginate();
			
		}
		
		
		$all_datas = [];

		foreach($serialGroup as $key => $value) {
			$ab = $a->where('serial_number',$value->serial_number)->orderBy('debut','asc')->get();
				
			$all_datas[$key] = [
				'serial'	=>	$value->serial_number,
				'abonnements'	=>	$ab->count() > 0 ? $ab : null
			];
		}
		
		$thisMonthAbonnementEnd = [];
		$thisMonthAbonnementInactif = [];
		$i=0;
		$j =0;

		foreach($all_datas as $_key => $_value) {
			if(!is_null($_value['abonnements'])) {
				$flag = 0;
				$tmp = null;
				$futur = null;
				foreach($_value['abonnements'] as $key => $value) {
					// recuperer l'abonnement en cours
					$debut = new Carbon($value->debut);
					$fin = new Carbon($value->debut);
					$fin->addMonths($value->duree)->subDay()
						->addHours(23)
						->addMinutes(59)
						->addSeconds(59);

						
					if($debut <= $now && $now <= $fin) {
						$endDiff = $fin->diffInDays($now);
						// abonnement en cours
						if($endDiff > 0 && $endDiff <= 10) {
							$tmp = $value;
							$flag++;
						}
					}

					if($now < $debut) {
						// abonnement futur
						$futur = $value;
						$flag++;
					}					

				}

				if($flag == 1) {
					// 
					if(!is_null($tmp)) {

						$thisMonthAbonnementEnd[$i] = $tmp;
						$i++;
					}
				}
			}
			// last abonnement 
			$last = $_value['abonnements']->last();
			// recuperation de tous les abonnements interrompue
			$_debut = new Carbon($last->debut);
			$_fin = new Carbon($last->debut);
			$_fin->addMonths($last->duree)->subDay()
				->addHours(23)
				->addMinutes(59)
				->addSeconds(59);
			
			if($now > $_fin) {
				$thisMonthAbonnementInactif[$j] = $last;
				$j++;
			}
		}
		return [
			'relance'	=>	$thisMonthAbonnementEnd,
			'inactif'	=>	$thisMonthAbonnementInactif,
			'data'	=>	$serialGroup
		];
	}
	
	public function getAlertAbonnementForAllUsers(Request $request,Abonnement $a) {
		try {
			$now = Carbon::now();
			$data =[];
			$data_inactif = [];
			foreach($this->filterAlertAbonnement($request,$a,$now)['relance'] as $key => $value) {
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
			
			foreach($this->filterAlertAbonnement($request,$a,$now)['inactif'] as $key => $value) {
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
					'inactif'	=>	$data_inactif,
					'next_url'	=> $this->filterAlertAbonnement($request,$a,$now)['data']->nextPageUrl(),
					'last_url'	=> $this->filterAlertAbonnement($request,$a,$now)['data']->previousPageUrl(),
					'per_page'	=>	$this->filterAlertAbonnement($request,$a,$now)['data']->perPage(),
					'current_page'	=>	$this->filterAlertAbonnement($request,$a,$now)['data']->currentPage(),
					'first_page'	=>	$this->filterAlertAbonnement($request,$a,$now)['data']->url(1),
					'first_item'	=>	$this->filterAlertAbonnement($request,$a,$now)['data']->firstItem(),
					'total'	=>	$this->filterAlertAbonnement($request,$a,$now)['data']->total(),
					]);

		} catch(AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}

}