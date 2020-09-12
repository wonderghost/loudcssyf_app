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
use Illuminate\Support\Facades\DB;

class CommandController extends Controller
{
    //
		use Similarity;
		use Livraisons;
		use Cga;
		use Afrocashes;
		// RECUPERATION DES INFORMATIONS LIEES AU MATERIAL
		public function infoMaterial(Request $request , Produits $p , RapportVente $r ,CompenseMaterial $c , CommandMaterial $cm) {
			try {
				$item = $p->where("with_serial",1)->first();
				$parabole = $p->where('with_serial',0)->first();

				// trouver le nombre de parabole a livrer

				$migration = $r->where('vendeurs',$request->user()->username)
					->where('type','migration')
					->sum('quantite');
				$command = $cm->select('id_commande')->where('vendeurs',$request->user()->username)->get();
				$compense = $c->whereIn('commande_id',$command)->sum('quantite');
				//

				$all = [
					'ttc'	=>	$item->prix_initial,
					'ht'	=>	ceil($item->prix_vente/1.18),
					'tva'	=>	ceil($item->prix_vente - ($item->prix_vente/1.18)),
					'marge'	=>	$item->marge,
					'subvention'	=>	$item->prix_initial - $item->prix_vente,
					'prix_vente'	=>	$item->prix_vente,
					'reference'	=>	$item->reference ,
					'migration'	=>	$migration - $compense,
					'compense'	=>	0
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
	public function addCommand() {
		return view('command.new-command');
	}
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
				$command_produit = new CommandProduit;
				$command_produit_parabole = new CommandProduit;

				$command->id_commande = "CM-".time();
				$command->vendeurs = Auth::user()->username;

				// cas des promos en cours
				if($request->input('promo_id') !== "") {
					$command->promos_id = $request->input('promo_id');
				}
				// ##
				$command_produit->commande = $command->id_commande;
				$command_produit_parabole->commande = $command->id_commande;

				$command_produit_parabole->produit = Produits::where('libelle','Parabole')->first()->reference;
				$command_produit->produit = $request->input('reference_material');
				
				// QUANTITE DE PARABOLE A LIVRER	
				$migration = RapportVente::where('vendeurs',Auth::user()->username)->where('type','migration')->sum('quantite');

				$commandAll = CommandMaterial::select('id_commande')->where('vendeurs',$request->user()->username)->get();
				$compense = CompenseMaterial::whereIn('commande_id',$commandAll)->sum('quantite');

					$parabole_a_livrer = $request->input('quantite') - ($migration - $compense);

					$command_produit_parabole->quantite_commande = $request->input('quantite');
					$command_produit_parabole->parabole_a_livrer = $parabole_a_livrer < 0 ? 0 : $parabole_a_livrer;

					$command_produit->quantite_commande = $request->input('quantite');
					$command_produit->parabole_a_livrer = $request->input('quantite');
					
					$transaction = new TransactionAfrocash;
					$transaction->command_material_id = $command->id_commande;

					$command->save();
					$command_produit->save();
					$command_produit_parabole->save();

					// DEBIT DU COMPTE AFROCASH DU VENDEUR / DA
					$afrocash_courant_vendeurs = Afrocash::where([
						'vendeurs'	=>	Auth::user()->username,
						'type'	=>	'courant'
					])->first();

					// CREDIT DU COMPTE AFROCASH LOGISTIQUE
					$afrocash_courant_logistique = Afrocash::where([
						'vendeurs'	=>	User::where('type','logistique')->first()->username,
						'type'	=>	'courant'
					])->first();

					$afrocash_courant_vendeurs->debitAccountAfrocash($request->input('prix_achat'));
					$afrocash_courant_logistique->creditAccountAfrocash($request->input('prix_achat'));

					$afrocash_courant_vendeurs->save();
					$afrocash_courant_logistique->save();

					$transaction->motif = "Commande Materiel";
					$transaction->compte_debite = $afrocash_courant_vendeurs->numero_compte;
					$transaction->compte_credite = $afrocash_courant_logistique->numero_compte;
					$transaction->montant = $request->input('prix_achat');

					$transaction->save();
					// ENREGISTREMENT DE LA NOTIFICATION
					$n = $this->sendNotification("Commande Materiel" ,"Il y a une commande en attente de confirmation pour : ".Auth::user()->localisation,User::where('type','admin')->first()->username);
					$n->save();
					$n = $this->sendNotification("Commande Materiel" ,"Votre commande est en attente de confirmation!",Auth::user()->username);
					$n->save();
					$n = $this->sendNotification("Commande Materiel" ,"Vous avez une commande en attente de la part de ".Auth::user()->localisation,User::where('type','logistique')->first()->username);
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

	public function DetailsCommand($id) {
		return view('command.details')->withId($id);
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
