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
use Illuminate\Support\Facades\Hash;
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
use App\PayCommission;


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

        $recrutement = RapportVente::where('vendeurs',Auth::user()->username)->where('type','recrutement')->orderBy('date_rapport','desc')->limit(30)->get();
        $reabonnement = RapportVente::where('vendeurs',Auth::user()->username)->where('type','reabonnement')->orderBy('date_rapport','desc')->limit(30)->get();
        $migration = RapportVente::where('vendeurs',Auth::user()->username)->where('type','migration')->orderBy('date_rapport','desc')->limit(30)->get();

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
// ENVOI DE LA DEMANDE DE PAIEMENT DE COMMISSION
public function payCommission(Request $request) {
  $validation = $request->validate([
    'password_confirm'  =>  'required'
  ],[
    'required'  =>  'Mot de passe requis'
  ]);
  try {

    if(Hash::check($request->input('password_confirm'),Auth::user()->password)) {
      $pay = new PayCommission;
      $rapport = RapportVente::where(['statut_paiement_commission'=>'non_paye','vendeurs' =>  Auth::user()->username])->orderBy('date_rapport','asc')->get();
      $pay->debut = $rapport->first()->date_rapport;
      $pay->fin = $rapport->last()->date_rapport;
      $pay->vendeurs = Auth::user()->username;
      $pay->montant_total=0;
      foreach($rapport as $key=>$value) {
        $pay->montant_total+= $value->commission;
      }
      if($pay->isExistPayment()) {
        throw new AppException("Une demande de paiement existe deja , Revenez Plus tard !");
      }
      $pay->save();
      // VENDEURS
      $this->sendNotification("Paiement Commission","Vous avez envoye une demande de paiement de commission!",Auth::user()->username);
      // GESTIONNAIRE DE CREDIT
      $this->sendNotification("Paiement Commission","Il y a une demande de paiement de commission de la part de : ".Auth::user()->localisation,User::where("type",'gcga')->first()->username);
      return response()->json('done');
    } else {
      throw new AppException("Mot de Passe invalide!");
    }
  } catch (AppException $e) {
    header("unprocessible entity",true,422);
    die(json_encode($e->getMessage()));
  }

}

}