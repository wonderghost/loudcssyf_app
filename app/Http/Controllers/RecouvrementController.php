<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\TransactionAfrocash;
use App\Afrocash;
use Carbon\Carbon;
use App\Exceptions\AppException;
use App\Recouvrement;

class RecouvrementController extends Controller
{
    //

    public function operations() {
      $users = User::where('type','v_standart')->get();
      return view('recouvrements.operations')->withUsers($users);
    }

    public function addRecouvrement(Request $request) {
      $validation = $request->validate([
        'vendeurs'  =>  'required|exists:users,username',
        'montant' =>  'required|numeric',
        'numero_recu' =>  'required|string|unique:recouvrements,numero_recu'
      ]);

      try {
        if($request->input('montant_du') === $request->input('montant')) {
          $recouvrement = new Recouvrement;
          $recouvrement->makeId();
          $recouvrement->montant = $request->input('montant');
          $recouvrement->numero_recu = $request->input('numero_recu');
          $recouvrement->vendeurs = $request->input('vendeurs');

          $temp = $recouvrement->id;

          $recouvrement->save();
          // AJOUT DE L'ID DE RECOUVREMENT DANS LA TABLE TRANSACTIONS
          TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where([
            'type'  =>  'semi_grossiste',
            'vendeurs'  =>  $request->input('vendeurs')
          ])->get())->where('recouvrement',NULL)->update([
            'recouvrement'  =>  $temp
          ]);

          return redirect('user/recouvrement')->withSuccess("Success!");
        } else {
          throw new AppException("Erreur sur le montant!");
        }
      } catch (AppException $e) {
        return back()->with("_errors",$e->getMessage());
      }
    }

    public function allTransactions(Request $request) {
      try {
        if($request->input('ref-0') == "all") {
          if($request->input('ref-1') == "all") {
            $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('type','semi_grossiste')->get())->get();
          } else {
            if($request->input('ref-1') == "recouvre") {
              $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('type','semi_grossiste')->get())->whereNotNull('recouvrement')->get();
            } else {
              $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('type','semi_grossiste')->get())->whereNull('recouvrement')->get();
            }
          }
        } else {
          if($request->input('ref-1') == "all") {
            $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('vendeurs',$request->input('ref-0'))->where('type','semi_grossiste')->get())->get();
          } else {
            if($request->input('ref-1') == "recouvre") {
              $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('vendeurs',$request->input('ref-0'))->where('type','semi_grossiste')->get())->whereNotNull('recouvrement')->get();
            } else {
              $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('vendeurs',$request->input('ref-0'))->where('type','semi_grossiste')->get())->whereNull('recouvrement')->get();
            }
          }
          // $transactions = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where('type','semi_grossiste')->where('vendeurs',$request->input('ref-0'))->get())->get();
        }
        $all = [];
        foreach($transactions as $key => $value) {
          $date = new Carbon($value->created_at);
          $date->setLocale('fr_FR');
          $all[$key]  = [
            'date'  =>  $date->toFormattedDateString()." (".$date->diffForHumans().") ",
            'expediteur'  => $value->afrocash()->vendeurs()->localisation,
            'destinataire'  => $value->afrocashcredite()->vendeurs()->localisation,
            'montant' =>  number_format($value->montant),
            'type'  =>  'depot',
            'status'  =>  is_null($value->recouvrement) ? 'non effectue' : 'effectue'
          ];

        }
        return response()->json($all);
      } catch (AppException $e) {
        header("Unprocessible entity", true,422);
        die($e->getMessage());
      }
    }

    // RECUPERATION DU MONTANT DU

    public function getMontantDuRecouvrement(Request $request) {
      try {
        $total = TransactionAfrocash::whereIn('compte_debite',Afrocash::select('numero_compte')->where([
          'type'  =>  'semi_grossiste',
          'vendeurs'  =>  $request->input('ref-0')
        ])->get())->where('recouvrement',NULL)->sum('montant');
        return response()->json($total);
      } catch (AppException $e) {
        header("Unprocessible entity",true,422);
        die($e->getMessage());
      }
    }

    // TOUS LES RECOUVREMENTS
    public function allRecouvrement(Request $request) {
      try {
        if($request->input('ref-0') == "all") {
          $recouvrement = Recouvrement::all();
        } else {
          $recouvrement = Recouvrement::where('vendeurs',$request->input('ref-0'))->get();
        }
        $all=[];
        foreach($recouvrement as $key=>$value) {
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
        header("Unprocessible entity",true,422);
        die($e->getMessage());
      }

    }
}
