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
use App\TransactionAfrocash;


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
// @@@@@
  public function rapportVendeur($type='reabonnement',$vendeur) {
    $data = RapportVente::where(['type'=>$type,'vendeurs'=>$vendeur])->orderBy('date_rapport','desc')->paginate(10);
    return  $this->organizeRapport($data);
  }

  public function reabonnementList() {
    try {
      return response()->json($this->rapportVendeur('reabonnement',Auth::user()->username));
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function recrutementList() {
    try {
      return response()->json($this->rapportVendeur('recrutement',Auth::user()->username));
    } catch (AppException $e) {
      header("Erreur !",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function migrationList() {
    try {
      return response()->json($this->rapportVendeur('migration',Auth::user()->username));
    } catch (AppException $e) {
      header("Erreur !",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function totalCommissionVendeur() {
    try {
      $commission = number_format(RapportVente::whereIn('type',['recrutement','reabonnement'])->where("vendeurs",Auth::user()->username)->where('statut_paiement_commission','non_paye')->sum('commission'));
      return response()->json($commission);
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }

  }

// @@@@@@
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
      if($rapport->count() > 0) {
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
        throw new AppException("Vous n'avez pas de commission !");
      }
    } else {
      throw new AppException("Mot de Passe invalide!");
    }
  } catch (AppException $e) {
    header("unprocessible entity",true,422);
    die(json_encode($e->getMessage()));
  }

}

// LIST DE PAIEMENT DES COMMISSIONS
public function PayCommissionList(Request $request) {
  try {
    if($request->input('ref-0') == "all") {
      $payCommission = PayCommission::all();
    }
    else {
      $payCommission = PayCommission::where("vendeurs",$request->input("ref-0"))->get();
    }
    $all =[];
    foreach($payCommission as $key  =>  $value) {
      $all[$key]  = [
        'id'  =>  $value->id,
        'du' => $value->debut,
        'fin' =>  $value->fin,
        'total' =>  number_format($value->montant_total),
        'status'  =>  $value->status,
        'vendeurs'  => $value->vendeurs()->localisation
      ];
    }

    return response()->json($all);
  } catch (AppException $e) {
    header("unprocessible entity",true,422);
    die(json_encode($e->getMessage()));
  }

}

// VALIDATION PAIEMENT DES COMMSSIONS

public function validatePayComission(Request $request) {
  try {
    $validation = $request->validate([
      'password_confirm'  =>  'required',
      'pay_comission_id'  =>  'required|exists:pay_commissions,id'
    ],[
      'required'  =>  'Remplissez les champs vides'
    ]);
    if(Hash::check($request->input('password_confirm'),Auth::user()->password)) {
      // LE MOT DE PASSE CORRESPOND
      $comission = PayCommission::find($request->input('pay_comission_id'));
      // RECUPERATION DES RAPPORTS DANS L'INTERVAL DE DATE
      $rapport = RapportVente::whereBetween('date_rapport',[$comission->debut,$comission->fin])->where('vendeurs',$comission->vendeurs)->where('statut_paiement_commission','non_paye')->get();
      $total = RapportVente::whereBetween('date_rapport',[$comission->debut,$comission->fin])->where('vendeurs',$comission->vendeurs)->where('statut_paiement_commission','non_paye')->sum('commission');
      $afrocash_account = Afrocash::where([
        'type'  =>  'courant',
        'vendeurs'  =>  $comission->vendeurs
      ])->first();
      $afrocash_central = Credit::find('afrocash');
      if($rapport->count() <= 0) {
        $comission->status = 'validated';
        $comission->save();
        throw new AppException("Demande deja traitee!");
      }
      // return response()->json([$total,$comission->montant_total]);
      // die();
      if($total === $comission->montant_total) {
        // LES MONTANTS SONT IDENTIQUES , IL N'Y A PAS DE CONFUSION
        if($afrocash_central && ($afrocash_central->solde >= $total) ) {

          // ENVOI DES NOTIFICATIONS
          $this->sendNotification("Paiement Comission","Paiement de commission de : ".number_format($total)." GNF effectue pour :".$afrocash_account->vendeurs()->localisation,Auth::user()->username);
          $this->sendNotification("Paiement Comission","Paiement de commission de : ".number_format($total)." GNF effectue pour :".$afrocash_account->vendeurs()->localisation,User::where('type','admin')->first()->username);
          $this->sendNotification("Paiement Comission","Paiement de commission du : ".$comission->debut." au : ".$comission->fin." a hauteur de : ".number_format($total)." GNF recu avec success!",$comission->vendeurs);
          //
          $afrocash_central->solde-=$total;
          $afrocash_account->solde+=$total;
          $comission->status = 'validated';
          $comission->save();
          $afrocash_central->save();
          $afrocash_account->save();
          // ENREGISTREMENT DE LA TRANSACTION
          $transaction_pay_comission = new TransactionAfrocash;
          $transaction_pay_comission->compte_credite = $afrocash_account->numero_compte;
          $transaction_pay_comission->montant = $total;
          $transaction_pay_comission->save();
          // CHANGEMENT DE STATUS DES RAPPORTS
          $rapport->each(function ($element,$index) {
            $element->changeStatePayComission();
            $element->save();
          });

          return response()->json('done');
        } else {
          throw new AppException("Montant Indisponible!");
        }
      }else {
        throw new AppException("Erreur , Ressayez plus tard !");
      }
    } else {
      throw new AppException("Erreur Sur le mot de passe saisi!");
    }
  } catch (AppException $e) {
    header("Erreur!",true,422);
    die(json_encode($e->getMessage()));
  }

}

}
