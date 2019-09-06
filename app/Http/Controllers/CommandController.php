<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produits;
use App\Http\Requests\CommandRequest;
use App\CommandMaterial;
use Illuminate\Support\Facades\Auth;
use App\RapportVente;
use App\Compense;
use App\Exemplaire;
use App\CommandProduit;

class CommandController extends Controller
{
    //

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
		// recuperation du terminal

		$test = Exemplaire::where('status','inactif')->first();
		$par = Produits::where("libelle",'parabole')->first();
		if($test && $par) {

			$terminal	=	Produits::where('reference',$test->produit)->first();

		// $material = Produits::all();
		$material = $terminal;
		$migration = RapportVente::where('vendeurs',Auth::user()->username)->sum('quantite_migration');
		$compense 	= Compense::where([
			'type'=>'debit',
			'vendeurs'	=>	Auth::user()->username
			])->get()->sum('quantite');
		// dd($compense);
		if($this->isExistCommandEnAttente()) {
			return redirect('/user/list-command')->with('_errors',"Vous avez une commande en attente de confirmation!");
		}
		return view('command.new-command')->withMaterial($material)->withMigration($migration)->withCompense($compense);
	}
	return redirect('user/list-command')->with("_errors","Indisponible pour le moment! Contactez l'administrateur");
	}

	public function sendCommand(CommandRequest $request) {

		$command = new CommandMaterial;// CREATION DE LA COMMANDE
		$command_produit = new CommandProduit;
		$command_produit_parabole = new CommandProduit;

		$command->id_commande = "CM-".time();
		$command->numero_versement = $request->input('numero_versement');
		$command->vendeurs = Auth::user()->username;

		$command_produit->commande = $command->id_commande;
		$command_produit_parabole->commande = $command->id_commande;

		$command_produit_parabole->produit = Produits::where('libelle','Parabole')->first()->reference;
		$command_produit->produit = $request->input('mat-reference');

		// QUANTITE DE PARABOLE A LIVRER
		$migration = RapportVente::where('vendeurs',Auth::user()->username)->sum('quantite_migration');
		$compense = Compense::where([
			'vendeurs'	=>	Auth::user()->username,
			'materiel'	=>	Produits::where('libelle','Parabole')->first()->reference
			])->sum('quantite');

		$parabole_a_livrer = $request->input('quantite') - ($migration + $compense);


		$command_produit_parabole->quantite_commande = $request->input('quantite');
		$command_produit_parabole->parabole_a_livrer = $parabole_a_livrer;

		$command_produit->quantite_commande = $request->input('quantite');
		$command_produit->parabole_a_livrer = $request->input('quantite');

		if($request->hasFile('recu')) {

			$tmp = $request->file('recu');
			$extension = $tmp->getClientOriginalExtension();
			$command->image = 'cmd'.time().'.'.$extension;
	      if($request->file('recu')->move(config('image.path'),$command->image)) {
	    	$command->save();
				$command_produit->save();
				$command_produit_parabole->save();
	    	return redirect('/user/new-command')->with('success','Commande envoyée!');
	    }
		}
	}


	public function getList() {
		return view('command.list-command');
	}

// LIST COMMANDES CHEZ LE VENDEURS
	public function getRequestList(Request $request) {
		$commands= CommandMaterial::where('vendeurs',Auth::user()->username)->get();
		foreach($commands as $key => $values) {
			$produit = Produits::select()->where('reference',$values->produit)->first();
			$comm_produit = CommandProduit::where('commande',$values->id_commande)->first();
			$all [$key] = [
				'item' => 'Kit Complet',
				'quantite' => $comm_produit->quantite_commande,
				'numero_recu' => $values->numero_versement,
				'status' =>  ($values->status == 'unconfirmed') ? 'en attente' : 'confirmer',
				'id_commande' => $values->id_commande
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
		$migration = RapportVente::where('vendeurs',Auth::user()->username)->sum('quantite_migration');
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
