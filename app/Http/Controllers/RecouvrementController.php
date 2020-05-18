<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\TransactionAfrocash;
use App\Afrocash;
use Carbon\Carbon;
use App\Exceptions\AppException;
use App\Recouvrement;
use App\Traits\Similarity;
use Illuminate\Support\Facades\Auth;
use App\CommandCredit;

class RecouvrementController extends Controller
{
    //
    use Similarity;

    public function operations() {
      return view('recouvrements.operations');
    }

    public function addRecouvrement(Request $request , TransactionAfrocash $ta) {
      $validation = $request->validate([
        'vendeurs'  =>  'required|exists:users,username',
        'montant' =>  'required|numeric',
        'numero_recu' =>  'required|string|unique:recouvrements,numero_recu'
      ]);

      try {
        if($request->input('montant_du') <= 0) {
          throw new AppException("Recouvrement Impossible !");
        }
        if($request->input('montant_du') == $request->input('montant')) {
          // VERIFIER SI LE MONTANT RECU EST EGAL AU MONTANT CALCULER
          $total = $ta->whereIn('compte_debite',Afrocash::select('numero_compte')->where([
            'type'  =>  'semi_grossiste',
            'vendeurs'  =>  $request->input('vendeurs')
          ])->get())->whereNull('recouvrement')->sum('montant');

          if($request->input('montant') != $total) {
            throw new AppException("Erreur Veuillez ressayer ulterieurment , Il semble qu'une transaction ait eu lieu , veuillez reprendre la procedure!");
          }

          // @@@@@@@@@@@@@@@2
          $recouvrement = new Recouvrement;
          $recouvrement->makeId();
          $recouvrement->montant = $request->input('montant');
          $recouvrement->numero_recu = $request->input('numero_recu');
          $recouvrement->vendeurs = $request->input('vendeurs');

          $temp = $recouvrement->id;

          // ENVOI DE LA NOTIFICATION
          $n = $this->sendNotification("Recouvrement","Recouvrement de : ".number_format($recouvrement->montant)." GNF effectue !",$recouvrement->vendeurs);
          $n->save();
          $n = $this->sendNotification("Recouvrement","Vous avez effectue un recouvrement de ".number_format($recouvrement->montant)." GNF pour : ".$recouvrement->vendeurs()->localisation,Auth::user()->username);
          $n->save();
          $n = $this->sendNotification("Recouvrement","Recouvrement effectue au compte de : ".$recouvrement->vendeurs()->localisation,'admin');
          $n->save();

          $recouvrement->save();

          $cc = new CommandCredit;
          
          $this->sendAutoCommandeAfrocash(
            $cc,
            $recouvrement->montant,
            $recouvrement->vendeurs,
            $recouvrement->numero_recu
          );
          // AJOUT DE L'ID DE RECOUVREMENT DANS LA TABLE TRANSACTIONS

          TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where([
            'type'  =>  'semi_grossiste',
            'vendeurs'  =>  $request->input('vendeurs')
          ])->get())->where('recouvrement',NULL)->update([
            'recouvrement'  =>  $temp
          ]);

          return response()
            ->json('done');
        } else {
          throw new AppException("Erreur sur le montant!");
        }
      } catch (AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }
    }

    public function allTransactions(Request $request , TransactionAfrocash $ta) {
      try {
        $transactions = $ta->whereIn('compte_debite',Afrocash::select('numero_compte')->where('type','semi_grossiste')->get())
          ->orderBy('created_at','desc')->get();
        $all = [];
        foreach($transactions as $key => $value) {
          $date = new Carbon($value->created_at);
          $date->setLocale('fr_FR');
          $all[$key]  = [
            'date'  =>  $date->toFormattedDateString(),
            'expediteur'  => $value->afrocash()->vendeurs()->localisation,
            'destinataire'  => $value->afrocashcredite()->vendeurs()->localisation,
            'montant' =>  number_format($value->montant),
            'type'  =>  'depot',
            'status'  =>  is_null($value->recouvrement) ? 'non effectue' : 'effectue',
            'recu'  =>  $value->recouvrement() ?  $value->recouvrement()->numero_recu : ''
          ];

        }
        return response()->json($all);
      } catch (AppException $e) {
        header("Erreur!", true,422);
        die(json_encode($e->getMessage()));
      }
    }

    // RECUPERATION DU MONTANT DU

    public function getMontantDuRecouvrement(TransactionAfrocash $ta , $vendeur) {
      try {
        $total = $ta->whereIn('compte_debite',Afrocash::select('numero_compte')->where([
          'type'  =>  'semi_grossiste',
          'vendeurs'  =>  $vendeur
        ])->get())->whereNull('recouvrement')->sum('montant');
        return response()
          ->json($total);
      } catch (AppException $e) {
        header("Erreur!",true,422);
        die(json_encode($e->getMessage()));
      }
    }

    // TOUS LES RECOUVREMENTS
    public function allRecouvrement(Recouvrement $r) {
      try {
        $all=[];
        foreach($r->select()->orderBy('created_at','desc')->get() as $key=>$value) {
          $date = new Carbon($value->created_at);
          $date->setLocale('fr_FR');
          $all[$key]  = [
            'id'  =>  $value->id,
            'date'  =>  $date->toFormattedDateString()." (".$date->diffForHumans().")",
            'vendeurs'  =>  $value->vendeurs()->localisation,
            'montant' =>  number_format($value->montant),
            'numero_recu' =>  $value->numero_recu
          ];
        }
        return response()->json($all);
      } catch (AppException $e) {
        header("Erreur!",true,422);
        die(json_encode($e->getMessage()));
      }

    }

    #@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    public function sendAutoCommandeAfrocash(CommandCredit $cc , $montant , $vendeurs, $numero_recu) {
      try {

          $cc->montant = $montant;
          $cc->vendeurs = $vendeurs;
          $cc->type = 'afro_cash_sg';
          $cc->numero_recu = $numero_recu;
          $cc->save();

          $n = $this->sendNotification("Commande Afrocash" , "Vous avez envoyer une command Afrocash Grossiste",$vendeurs);
          $n->save();
          $n = $this->sendNotification("Commande Afrocash" , "Vous avez une commande Afrocash en attente de confirmation!",User::where('type','gcga')->first()->username);
          $n->save();
          $n = $this->sendNotification("Commande Afrocash" , "Il y a une commande Afrocash en attente de confirmation pour : ".User::where("username",$vendeurs)->first()->localisation,'admin');
          $n->save();
          return true;
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }
}
