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
use App\Abonnement;
use App\AbonneOption;

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

  public function getAllRapportForVendeur(Request $request , RapportVente $r) {
    try {
      $result = $r->where('vendeurs',$request->user()->username)
        ->orderBy('date_rapport','desc')->get();
      return response()
        ->json($this->organizeRapport($result));
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }
  }


  public function totalCommissionVendeur(Request $request , RapportVente $r) {
    try {
      $commission = $r->whereIn('type',['recrutement','reabonnement'])
        ->where('state','unaborted')
        ->where('vendeurs',$request->user()->username)
        ->whereNull('pay_comission_id')
        ->sum('commission');
      return response()->json($commission);
    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }

  }
// ----------------------------------------------------------------------------------------------
// @@@@@@
// ENVOI DE LA DEMANDE DE PAIEMENT DE COMMISSION
public function payCommission(Request $request , PayCommission $pay) {
  $validation = $request->validate([
    'password_confirm'  =>  'required'
  ],[
    'required'  =>  'Mot de passe requis'
  ]);
  try {
    if(Hash::check($request->input('password_confirm'),Auth::user()->password)) {


      // recuperation des identifiants de demande de paiement
      $_tmp = $request->user()->rapportGroupByPayId();
      $pay_request = PayCommission::whereIn('id',$_tmp)
        ->where('status','unvalidated')
        ->get();

      if($pay_request->count() > 0) {
        throw new AppException("Vous avez deja une demande de paiement de comissions en attente de traitement , Veuillez Ressayez plus tart :-)");
      }

      $rapport = $request->user()->rapportsPayNUll();
      $pay->id = "pay_comission_".$request->user()->username.time();
      if($rapport->count() > 0) {
        $pay->montant=0;
        foreach($rapport as $key=>$value) {
          $pay->montant+= $value->commission;
        }
        if($pay->montant < 100000) {
          // si le montant est valide !
          throw new AppException("Vous devez avoir au moins 100,000 GNF !");
        }
        $tmp = $pay->id;
        $pay->save();
        foreach($rapport as $key => $value ) {
          $value->pay_comission_id = $tmp;
          $value->save();
        }
        // VENDEURS
        $this->sendNotification("Paiement Commission","Vous avez envoye une demande de paiement de commission!",Auth::user()->username);
        // GESTIONNAIRE DE CREDIT
        $_user = User::where('type','gcga')->get();
        foreach($_user as $value) {
          $this->sendNotification("Paiement Commission","Il y a une demande de paiement de commission de la part de : ".Auth::user()->localisation,$value->username);
        }
        // ADMIN
        $this->sendNotification("Paiement Commission","Il y a une demande de paiement de commission de la part de : ".Auth::user()->localisation,'admin');
        $this->sendNotification("Paiement Commission","Il y a une demande de paiement de commission de la part de : ".Auth::user()->localisation,'root');
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
public function PayCommissionListForVendeurs(Request $request) {
  try {
    $_tmp = $request->user()->rapportGroupByPayId();
    $payCommission = PayCommission::whereIn('id',$_tmp)->get();
    $all =[];
    foreach($payCommission as $key  =>  $value) {
      $all[$key]  = [
        'id'  =>  $value->id,
        'du'=> $value->rapports()->get()->first()->date_rapport,
        'au'=> $value->rapports()->get()->last()->date_rapport,
        'total' =>  number_format($value->montant),
        'status'  =>  $value->status,
        'vendeurs'  => $value->rapports()->first()->vendeurs()->localisation
      ];
    }
    return response()->json($all);
  } catch (AppException $e) {
    header("unprocessible entity",true,422);
    die(json_encode($e->getMessage()));
  }

}


  public function payComissionList(PayCommission $pay){
    try {
      $result = $pay->select()->orderBy('status','asc')->get();
      $all = [];
      foreach ($result as $key => $value) {
        $all[$key] = [
          'id'  =>  $value->id,
          'du'=> $value->rapports()->get()->first()->date_rapport,
          'au'=> $value->rapports()->get()->last()->date_rapport,
          'total' =>  number_format($value->montant),
          'status'  =>  $value->status,
          'vendeurs'  => $value->rapports()->first()->vendeurs()->localisation,
          'pay_at'  =>  $value->pay_at
        ];
      }

      return response()
        ->json($all);

    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }

  }


// VALIDATION PAIEMENT DES COMMSSIONS

public function validatePayComission(Request $request) {
  try {
    $validation = $request->validate([
      'password_confirm'  =>  'required',
      'pay_comission_id'  =>  'required|exists:pay_comissions,id'
    ],[
      'required'  =>  'Remplissez les champs vides'
    ]);
    if(Hash::check($request->input('password_confirm'),$request->user()->password)) {
      // LE MOT DE PASSE CORRESPOND
      $comission = PayCommission::find($request->input('pay_comission_id'));
      if($comission->status == 'validated') {
        throw new AppException("Deja valide!");
      }
      $rapport = $comission->rapports()->get();

      $total = $rapport->sum('commission');


      $afrocash_account = Afrocash::where([
        'type'  =>  'courant',
        'vendeurs'  =>  $rapport->first()->vendeurs
      ])->first();

      $afrocash_central = Credit::find('afrocash');

      if($total === $comission->montant) {
        // LES MONTANTS SONT IDENTIQUES , IL N'Y A PAS DE CONFUSION
        if($afrocash_central && ($afrocash_central->solde >= $total) ) {
          // ENVOI DES NOTIFICATIONS
          $_user_credit = User::where('type','gcga')->get();

          foreach($_user_credit as $value) {
            $this->sendNotification("Paiement Comission","Paiement de commission de : ".number_format($total)." GNF effectue pour :".$afrocash_account->vendeurs()->localisation,$value->username);
          }

          $this->sendNotification("Paiement Comission","Paiement de commission de : ".number_format($total)." GNF effectue pour :".$afrocash_account->vendeurs()->localisation,'admin');
          $this->sendNotification("Paiement Comission","Paiement de commission de : ".number_format($total)." GNF effectue pour :".$afrocash_account->vendeurs()->localisation,'root');

          $this->sendNotification("Paiement Comission","Paiement de commission a hauteur de : ".number_format($total)." GNF recu avec success!",$rapport->first()->vendeurs);
          //
          $afrocash_central->solde-=$total;
          $afrocash_account->solde+=$total;

          $comission->status = 'validated';
          $pay_at = Carbon::now();
          $comission->pay_at = $pay_at->toDateTimeString();
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

// voire les details d'un rapport de vente [recrutement | migration]

public function getDetailsForRapport(Request $request , RapportVente $r) {
  try {
      $details = $r->find($request->input('rapId'));
      return response()->json($details->exemplaire());
  } catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
  }
}

}
