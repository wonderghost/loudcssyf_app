<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\CreditRequest;
use App\Http\Requests\CgaRequest;
use App\Http\Requests\RexRequest;
use App\Credit;
use App\Traits\Similarity;
use App\User;
use App\CgaAccount;
use App\RexAccount;
use App\TransactionCga;
use App\TransactionRex;
use App\TransactionCredit;
use App\Agence;
class CreditController extends Controller
{
	use Similarity;
    //SOLDE DES VENDEURS

		public function soldeVendeur() {
			return view('credit.solde-vendeur');
		}

		public function getSoldeVendeur(Request $request) {
			$accounts = CgaAccount::all();
			$all = [];
			foreach ($accounts as $key => $value) {
				$user = User::where('username',$value->vendeur)->first();
				$agence = Agence::where('reference',$user->agence)->first();
				$all[$key]=$this->organizeSoldeVendeurs($value,$agence);
			}
			return response()->json($all);
		}

     // AJOUTER UN COMPTE
    public function addAccount() {
    	$credits = Credit::all();
        return view('admin.add-account-credit')->withCredit($credits);
    }

    //
    public function makeAddAccount(CreditRequest $request) {
    	// dd($request);
    	$credit = new Credit;
    	$credit->designation = $request->input('compte');
    	$credit->solde = $request->input('montant');
    	if($temp = $this->isExistCredit($credit->designation)) {
    		$solde = $temp->solde + $credit->solde;
    		Credit::select()->where('designation',$credit->designation)->update(
    			[
    				'solde' => $solde
    			]);
    	} else {
	    	$credit->save();
    	}
    	return redirect('/admin/add-account-credit')->with('success',"Compte $credit->designation Credité de ".number_format($credit->solde)." GNF !");
    }

    // CREDITER UN VENDEUR
    public function crediterVendeur() {
        $solde = Credit::select()->where('designation','cga')->first()->solde;
        return view('credit.crediter-vendeur')->withSolde($solde);
    }

    // CREDITER UN VENDEUR EN REX
    public function crediterVendeurRex() {
        $solde = Credit::select()->where('designation','rex')->first()->solde;
        return view('credit.crediter-vendeur-rex')->withSolde($solde);
    }

    // public function makeCrediterVendeur(CreditRequest $request) {
    //     dd($request);
    // }

    public function getListVendeur(Request $request) {
        $all = User::select()->where('type','v_da')->orWhere('type','v_standart')->get();
        if($all) {
            return response()->json($all);
        }
        return response()->json('fail');
    }

    public function isValidMontant($montant,$account = 'cga') {
        $temp = Credit::select()->where('designation',$account)->first();
        if($temp->solde >= $montant) {
            return $temp;
        }
        return false;
    }

    // // ENVOI DE CREDIT CGA
    public function sendCga(CgaRequest $request) {
        $cga = CgaAccount::select()->where('vendeur',$request->input('vendeur'))->first();
        // dd($cga);
        // die();
        // VERIFIER SI LE MONTANT EST VALIDE
        if($request->input('montant') && $request->input('montant') > 0) {
            // VERIFIER SI LE MONTANT EST DISPONIBLE
            if($temp = $this->isValidMontant($request->input('montant'))) {
                // DEBIT DANS LE SOLDE PRINCIPALE
                $soldeNow = $temp->solde - $request->input('montant');
                Credit::select()->where('designation','cga')->update([
                    'solde' => $soldeNow
                ]);
                //CREDIT DANS LE SOLDE VENDEUR
                $cga->solde = $cga->solde + $request->input('montant');
                CgaAccount::select()->where('vendeur',$request->input('vendeur'))->update([
                    'solde' => $cga->solde
                ]);
                // ENREGISTREMENT DE L'HISTORIQUE
                $histo = new TransactionCga;
                $histo->cga = $cga->numero;
                $histo->montant = $request->input('montant');
                $histo->save();
                return redirect('/user/cga-credit')->with('success',"Transaction effectuée !");
            } else {
                // MONTANT INDISPONIBLE
                return redirect('/user/cga-credit')->with('_errors',"Montant indisponible!");
            }

        } else {
            // MONTANT INVALIDE
            return redirect('/user/cga-credit')->with('_errors',"Montant invalide!");
        }
    }

    //  ENVOI DE CREDIT REX
    public function sendRex(RexRequest $request) {
        // dd(Auth::user());
        $rex = RexAccount::select()->where('numero',User::select()->where('username',$request->input('vendeur'))->first()->rex)->first();
        // VERIFIER SI LE MONTANT EST VALIDES
        if($request->input('montant') && $request->input('montant') > 0) {
            // VERIFIER SI LE MONTANT EST DISPONIBLE
            if($temp = $this->isValidMontant($request->input('montant'),'rex')) {
                // DEBIT DANS LE SOLDE PRINCIPALE
                $soldeNow = $temp->solde - $request->input('montant');
                Credit::select()->where('designation','rex')->update([
                    'solde' => $soldeNow
                ]);
                // CREDITER LE SOLDE REX
                $rex->solde = $rex->solde + $request->input('montant');
                RexAccount::select()->where('numero',$rex->numero)->update([
                    'solde' => $rex->solde
                ]);
                //
                $histo = new TransactionRex;
                $histo->rex = $rex->numero;
                $histo->montant = $request->input('montant');
                $histo->save();
                return redirect('/user/rex-credit')->with('success',"Transaction effectuée!");
            } else {
                return redirect('/user/rex-credit')->with('_errors',"Montant indisponible!");
            }
        } else {
            // MONTANT INVALIDE
            return redirect('/user/rex-credit')->with('_errors',"Montant invalide!");
        }
    }
}
