<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserEditRequest;
use App\Traits\Similarity;
use App\Traits\Afrocashes;
use App\Traits\Rapports;
use App\Traits\Cga;
use App\Http\Requests\FormuleRequest;
use App\Http\Requests\OptionRequest;
use App\Http\Requests\RapportRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Produits;
use App\Agence;
use App\Depots;
use App\Afrocash;
use App\RavitaillementDepot;
use App\CgaAccount;
use App\RexAccount;
use App\Formule;
use App\Option;
use App\RapportVente;
use App\StockVendeur;
use App\StockPrime;
use App\Exemplaire;
use App\Credit;
use App\Promo;
use App\TransactionCreditCentral;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Auth;


class RapportControlleur extends Controller
{
    //
    use Similarity;
    use Afrocashes;
    use Cga;
    use Rapports;

    public function getRapportByVendeurs() {
      return view('simple-users.rapport-vente');
    }

    public function getListRapport(Request $request) {

      try {

        $recrutement = RapportVente::where('vendeurs',Auth::user()->username)->where('type','recrutement')->orderBy('created_at','desc')->limit(30)->get();
        $reabonnement = RapportVente::where('vendeurs',Auth::user()->username)->where('type','reabonnement')->orderBy('created_at','desc')->limit(30)->get();
        $migration = RapportVente::where('vendeurs',Auth::user()->username)->where('type','migration')->orderBy('created_at','desc')->limit(30)->get();

        $rapports = [
	        'recrutement' =>  $recrutement,
	        'reabonnement'  => $reabonnement,
	        'migration' =>   $migration
	      ];

        $commission = number_format(RapportVente::whereIn('type',['recrutement','reabonnement'])->where('vendeurs',Auth::user()->username)->sum('commission'));
	      $all = [];

	      foreach ($rapports as $key => $value) {
	        foreach ($value as $_key => $_value) {

	          $all[$key][$_key] = [
	            'date'  =>  $_value->date_rapport,
	            'type'  =>  $_value->type,
	            'credit'  =>  $_value->credit_utilise,
	            'quantite'  =>  $_value->quantite,
	            'montant_ttc' =>  number_format($_value->montant_ttc),
	            'commission'  =>  number_format($_value->commission),
              'promo'	=>	$_value->promo > 0 ? '' : 'hors promo',
              'paiement_commission' =>  $_value->statut_paiement_commission
	          ];

	        }

	      }

        $all['commission'] = $commission;
	      return response()->json($all);
      } catch (AppException $e) {
        header("unprocessible entity",true,422);
        die($e->getMessage());
      }

    }


}
