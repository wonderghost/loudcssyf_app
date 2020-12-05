<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produits;
use App\Http\Requests\CommandRequest;
use App\CommandMaterial;
use Illuminate\Support\Facades\Auth;
use App\RapportVente;
use App\CompenseMaterial;
use App\Exemplaire;
use App\CommandProduit;
use App\Livraison;
use App\RavitaillementVendeur;
use Carbon\Carbon;
use App\Traits\Similarity;
use App\Traits\Livraisons;
use App\Traits\Cga;
use App\Traits\Afrocashes;
use App\Exceptions\AppException;
use App\CommandCredit;
use App\Afrocash;
use App\User;
use App\TransactionAfrocash;
use App\Notifications;
use App\Alert;
use App\Kits;

use Illuminate\Support\Facades\DB;

class CommandController extends Controller
{
    //
		use Similarity;
		use Livraisons;
		use Cga;
		use Afrocashes;
		// RECUPERATION DES INFORMATIONS LIEES AU MATERIAL

		public function getData(Kits $k) {
			try {
				$data = $k->all();
				$solde_afrocash = request()->user()->afroCash('courant')->first()->solde;
				return response()
					->json([
						'all'	=>	$data,
						'solde_afrocash'	=>	$solde_afrocash
					]);
			}
			catch(AppException $e) {
				header("Erreur",true,422);
				die(json_encode($e->getMessage()));
			}
		}

		public function infoMaterial($slug,Request $request , Produits $p , RapportVente $r ,CompenseMaterial $c , CommandMaterial $cm , Kits $k) {
			try {
				$dataKits = $k->find($slug);

				if(!$dataKits) {
					throw new AppException("Veuillez selectionner un article ...");
				}

				$array_materiel = $dataKits->articles()
					->get();

				$items = [];

				foreach($array_materiel as $key => $value) {
					$items[$key] =  $value->produits()->first();
				}

				$terminal = null;
				$accessoire = [];

				foreach($items as $value) {
					if($value->with_serial == 1) {
						$terminal = $value;
					}
					else {
						array_push($accessoire,$value);
					}
				}

				// trouver le nombre de parabole a livrer

				$rappVente = $r->select('id_rapport')
					->where('vendeurs',request()->user()->username)
					->where('type','migration')
					->groupBy('id_rapport')
					->get();

				$migration = 0;

				if(!is_null($terminal)) {

					$migration = Exemplaire::whereIn('rapports',$rappVente)
						->where('produit',$terminal->reference)
						->count();
				}
				

				$command = $cm->select('id_commande')->where('vendeurs',$request->user()->username)->get();

				$compense = $c->whereIn('commande_id',$command)->sum('quantite');

				//

				$accessoire_prix = [];

				foreach($accessoire as $key => $value) {
					$pi = 0;
					$pv = 0;

					$pi += $value->prix_initial;
					$pv += $value->prix_vente;

					$accessoire_prix = [
						'prix_initial'	=>	$pi,
						'prix_vente'	=>	$pv
					];
				}

				// MARGE EN FONCTION DES UTILISATEUR [DA/VSTANDART | PDC | PDDRAF]

				if(request()->user()->type == 'v_da' || request()->user()->type == 'v_standart') {
					// DA/ VSTANDART
					$marge = !is_null($terminal) ? $terminal->marge : 0;
				}
				else if(request()->user()->type == 'pdc') {
					$marge = !is_null($terminal) ? $terminal->marge_pdc : 0;
				}
				else if(request()->user()->type == 'pdraf') {
					$marge = !is_null($terminal) ? $terminal->marge_pdraf : 0;
				}
				#@@@@@@@@@@@@@@

				$all = [
					'ttc'	=>	!is_null($terminal) ? $terminal->prix_initial + $accessoire_prix['prix_initial'] : $accessoire_prix['prix_initial'],
					'ht'	=> !is_null($terminal) ? ceil($terminal->prix_vente/1.18) : $accessoire_prix['prix_vente'],
					'tva'	=> !is_null($terminal) ? ceil($terminal->prix_vente - ($terminal->prix_vente/1.18)) : ceil($accessoire_prix['prix_vente'] - $accessoire_prix['prix_vente'] / 1.18),
					'marge'	=>	$marge,
					'subvention'	=> !is_null($terminal) ? ($terminal->prix_initial - $terminal->prix_vente) + ($accessoire_prix['prix_initial'] - $accessoire_prix['prix_vente']) : 0,
					'prix_vente'	=> !is_null($terminal) ? $terminal->prix_vente : $accessoire_prix['prix_vente'],
					'reference'	=>	$dataKits->slug ,
					'migration'	=>	$migration - $compense > 0 ? $migration - $compense : 0,
					'compense'	=>	0,
				];
				
				return response()
					->json($all);
			} catch (AppException $e) {
				header("Erreur!",true,422);
				die(json_encode($e->getMessage()));
			}

		}
		// VERIFICATION S'IL N'EXISTE PAS UNE COMMANDE EN ATTENTE
		public function isExistCommandEnAttente() {
			$temp = CommandMaterial::where([
				'vendeurs'	=>	Auth::user()->username,
				'status'	=>	'unconfirmed'
				])->first();
				if($temp) {
					return $temp;
				}
				return false;
		}

		// @###
	
// ENVOI D'UNE COMMANDE MATERIEL
	public function sendCommand(CommandRequest $request) {
		try {
			// VERIFIER SI LE REMBOURSEMENT EST EFFECTIF APRES LA PROMO
			
			// VERIFIER LA DISPONIBILITE DE L'AFROCASH
			// verifier la disponibilite du montant dans le compte afrocash
			$afrocash_account = $this->getAfrocashAccountByUsername($request->user()->username);
			if(!$afrocash_account) {
				throw new AppException("Compte Afrocash inexistant!");
			}
			if($afrocash_account->solde < $request->input('prix_achat')) {
				throw new AppException("Solde Indisponible!");
			}
			// @@@@@

			if(!$this->isExistCommandEnAttente()) {
				$command = new CommandMaterial;// CREATION DE LA COMMANDE
				$commandByProduit = [];

				$command->id_commande = "CM-".time();
				$command->vendeurs = Auth::user()->username;

				// cas des promos en cours
				if($request->input('promo_id') !== "") {
					$command->promos_id = $request->input('promo_id');
				}
				// ##

				// 
				// RECUPERATION DES PRODUITS DE L'ARTICLE COMMANDE
				##@@@@@@@@@@@@@@@@@@@@@@@@@ GET INFO KIT @@@@@@@@@@@@@@@@@@@@@@@@@@

				$kit = Kits::find($request->input('reference_material'));

				$terminal = $kit->getTerminalReference();
				$accessoire = $kit->getAccessoryReference();

				##@@@@@@@@@@@@@@@@@@@@@@@@@@

				if($terminal) {

					$commandTerminal = new CommandProduit;
					$commandTerminal->commande = $command->id_commande;
					$commandTerminal->produit = $terminal->reference;
					$commandTerminal->quantite_commande = request()->quantite;
					$commandTerminal->parabole_a_livrer = request()->quantite;
				}


				foreach($accessoire as $key => $value) {
					$commandByProduit[$key] = new CommandProduit;
					$commandByProduit[$key]->commande = $command->id_commande;
					$commandByProduit[$key]->produit = $value->reference;
					$commandByProduit[$key]->quantite_commande = request()->quantite;
					
				}


				
				
				// QUANTITE DE PARABOLE A LIVRER	

				foreach($accessoire as $key => $value) {
					$commandByProduit[$key]->parabole_a_livrer = $request->input('parabole_du') <= 0 ? 0 : $request->input('parabole_du');
				}
				
				#DEBIT DU COMPTE DU VENDEURS ET CREDIT DU COMPTE DE LA LOGISTIQUE

				$sender_account = request()->user()->afroCash('courant')->first();
				
				$receiver_account =	User::where('type','logistique')
					->first()
					->afroCash('courant')
					->first();


				$sender_account->solde -= request()->prix_achat;
				$receiver_account->solde += request()->prix_achat;


				$transaction = new TransactionAfrocash;
				$transaction->command_material_id = $command->id_commande;
				$transaction->motif = "Commande Materiel";
				$transaction->compte_debite = $sender_account->numero_compte;
				$transaction->compte_credite = $receiver_account->numero_compte;
				$transaction->montant = request()->prix_achat;

				$command->save();

				if($terminal) {
					$commandTerminal->save();
				}

				foreach($commandByProduit as $value) {
					$value->save();
				}

				$sender_account->update();
				$receiver_account->update();
				$transaction->save();
				
				// ENREGISTREMENT DE LA NOTIFICATION
				
				$n = $this->sendNotification("Commande Materiel" ,"Il y a une commande en attente de confirmation pour : ".request()->user()->localisation,User::where('type','admin')->first()->username);
				$n->save();
				$n = $this->sendNotification("Commande Materiel" ,"Votre commande est en attente de confirmation!",request()->user()->username);
				$n->save();
				$n = $this->sendNotification("Commande Materiel" ,"Vous avez une commande en attente de la part de ".request()->user()->localisation,User::where('type','logistique')->first()->username);
				$n->save();
						

				return response()
					->json('done');
			} else {
				throw new AppException("Vous avez une commande en attente de confirmation!");
			}
		} catch (AppException $e) {
			header("Erreur",true,422);
			die(json_encode($e->getMessage()));
		}
	}
	
// LIST COMMANDES CHEZ LE VENDEURS
	public function getRequestList(Request $request) {
		$commands= CommandMaterial::where('vendeurs',Auth::user()->username)->get();
		$all = [];
		foreach($commands as $key => $values) {
			$produit = Produits::select()->where('reference',$values->produit)->first();
			$comm_produit = CommandProduit::where('commande',$values->id_commande)->first();
			$date = new Carbon($values->created_at);
			$livraison = $values->ravitaillements();
			$all [$key] = [
				'date'	=>	$date->toFormattedDateString(),
				'item' => 'Kit Complet',
				'quantite' => $comm_produit->quantite_commande,
				'numero_recu' => $values->numero_versement,
				'status' =>  ($values->status == 'unconfirmed') ? 'en attente' : 'confirmer',
				'id_commande' => $values->id_commande,
				'status_livraison'	=>	$livraison->count()
			];
		}
		return response()->json($all);
	}

// RECUPERATION DES DETAILS DE LA COMMANDE
	public function getDetailsCommand(Request $request) {
		$details = CommandMaterial::select()->where('id_commande',$request->input('ref'))->first();
		$mat = CommandProduit::where('commande',$request->input('ref'))->first();
		$migration = RapportVente::where('vendeurs',Auth::user()->username)->where('type','migration')->sum('quantite');
		$compense = Compense::where([
			'vendeurs'	=>	Auth::user()->username,
			'materiel'	=>	Produits::where('libelle','Parabole')->first()->reference
			])->sum('quantite');
			// QUANTITE A LIVRER
		$parabole_a_livrer = $mat->quantite - ($migration - $compense);
		$finale = [
			'material' => "Kit Complet",
			'quantite' => $mat->quantite_commande,
			'numero_recu' => $details->numero_versement,
			'recu' => $details->image,
			'parabole_du' => $parabole_a_livrer
		];
		return response()->json($finale);
	}
}
