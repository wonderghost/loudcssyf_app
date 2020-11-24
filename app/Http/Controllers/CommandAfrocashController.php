<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CommandAfrocash;
use App\Articles;
use App\Kits;
use App\ReaboAfrocashSetting;
use App\User;
use App\StockVendeur;

use App\Exceptions\AppException;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;


class CommandAfrocashController extends Controller
{
    //

     // COMMANDE AFROCASH LIST

    public function commandAfrocashList() {
        try {
            $user = request()->user();
            if($user->type == 'pdc' || $user->type == 'pdraf') {

                $commandId = CommandAfrocash::select('id_commande')
                    ->where('user_id',$user->username)
                    ->where('state',false)
                    ->where('remove_state',false)
                    ->distinct('id_commande')
                    ->paginate();
            }
            else {
                //
                $commandId = CommandAfrocash::select('id_commande')
                    ->where('state',false)
                    ->where('remove_state',false)
                    ->distinct('id_commande')
                    ->paginate(); 
            }

            $data = [];

            foreach($commandId as $key => $value) {

                $response = CommandAfrocash::find($value);
                $produitRef = [];
                foreach($response as $_value) {
                    array_push($produitRef,$_value->only('produit_id'));
                }

                $article = Articles::select('kit_slug')
                    ->whereIn('produit',$produitRef)
                    ->groupBy('kit_slug')
                    ->first();

                $kit = $article->kits()->first();
                

                $date = new Carbon($response->first()->created_at);

                $data[$key] = [
                    'id'    =>  Crypt::encryptString($response->first()->id_commande),
                    'date'  =>  $date->toDateString(),
                    'heure' =>  $date->toTimeString(),
                    'vendeur'   =>  $response->first()->user_id()->first()->localisation,
                    'article'   =>  $kit->name,//$response->first()->produit_id()->first()->libelle,
                    'quantite'  =>  $response->first()->quantite,
                    'status'    =>  $response->first()->state > 0 ? 'success' : 'instance',
                    'livraison' =>  $response->first()->livraison()->first()->state > 0 ? 'success' : 'instance',
                    'confirm_code'  =>  $response->first()->livraison()->first()->confirm_code
                ];

            }
            
            return response()
                ->json([
                    'all'   =>  $data,
                    'next_url'	=> $commandId->nextPageUrl(),
					'last_url'	=> $commandId->previousPageUrl(),
					'per_page'	=>	$commandId->perPage(),
					'current_page'	=>	$commandId->currentPage(),
					'first_page'	=>	$commandId->url(1),
					'first_item'	=>	$commandId->firstItem(),
					'total'	=>	$commandId->total()
                ]);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // INITIALISATION DES INFORMATIONS SUR LA PAGE DE CONFIRMATION DES COMMANDES AFROCASH

    public function getDataConfirmCommand($slug) {
        try {
            $id = Crypt::decryptString($slug);
            $response = CommandAfrocash::select('produit_id','quantite','quantite_a_livrer')
                ->where('id_commande',$id)
                ->where('state',false)
                ->get();
            
            $data = [];

            if($response->count() <= 0) {
                throw new AppException("Erreur !");
            }

            foreach($response as $key => $value) {
                $data[$key] = [
                    'item'  =>  $value->produit_id()->first()->libelle,
                    'quantite'  =>  $value->quantite,
                    'quantite_a_livrer' =>  $value->quantite_a_livrer,
                ];
            }

            return response()
                ->json($data);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function confirmCommandAfrocash($slug) {
        try {
            $validation = request()->validate([
                'serials.*' =>  'required|string|distinct|exists:exemplaire,serial_number',
                'confirm_code'  =>  'required|string|exists:livraison_afrocashes,confirm_code',
                'password_confirmation'  => 'required|string'
            ],[
                'required'  =>  '`:attribute` requi(s)',
                'exists'    =>  '`:attribute` n\'existe pas dans le systeme !',
                'distinct'  =>  'Vous avez dupliquer un champ(s) `:attribute`'
            ]);

            // VERIFIER LA VALIDITE DU MOT DE PASSE
            if(!Hash::check(request()->password_confirmation,request()->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $idCommande = Crypt::decryptString($slug);
            $command_afrocash = CommandAfrocash::where('id_commande',$idCommande)
                ->where('state',false)
                ->where('remove_state',false);
        
            $command_afrocash_produit_id = $command_afrocash->select('produit_id')
                ->get();            

            // VERIFICATION DE L'EXISTENCE DES NUMEROS DANS LE STOCK VENDEURS DU RECEPTEUR AFROCASH ET DE LA DISPONIBILITE

            $reabo_afrocash_setting = ReaboAfrocashSetting::all()->first();
            $recepteur_afrocash = User::where('username',$reabo_afrocash_setting->user_to_receive)->first();
            
            foreach(request()->serials as $key => $value) {
                
                $serialStockAfrocash[$key] = $recepteur_afrocash->exemplaire()
                    ->where('status','inactif')
                    ->whereNull('rapports')
                    ->where('serial_number',$value)
                    ->whereNull('pdc_id')
                    ->whereIn('produit',$command_afrocash_produit_id)
                    ->first();

                if(!$serialStockAfrocash[$key]) {
                    throw new AppException("Erreur Materiel ... Verifiez qu'il n'est pas deja attribuer !");
                }
            }

            // VERIFICATION DE LA VALIDITE DU CODE DE CONFIRMATION
            

            $command_data = $command_afrocash
                ->select()
                ->get();

            $livraison_afrocash = $command_data->first()->livraison()
                ->where('confirm_code',request()->confirm_code)
                ->where('state',false)
                ->first();

            if(!$livraison_afrocash) {
                throw new AppException("Erreur sur le code de confirmation !");
            }

            // TRAITEMENT DE L'OPERATION 

            # ECRITURE DES NUMEROS CHOISIS DANS UN FICHIER POUR STOCKAGE

            $livraison_afrocash->files = $idCommande.'.txt';

            $file = config('serial_file.path')."/".$livraison_afrocash->files;

            $handle = fopen($file,'w');

            foreach(request()->serials as $value) {
                fputs($handle,$value."\n");
            }

            fclose($handle);

            #   ATTRIBUTION DES NUMEROS AU PDC

            foreach($serialStockAfrocash as $value) {
                $value->pdc_id = $command_data->first()->user_id;
            }

            #MISE A JOUR DU STOCK
            $stock_vendeur = StockVendeur::where('vendeurs',$command_data->first()->user_id)
                ->first();

            $stock = [];

            if($stock_vendeur) {
                // LE STOCK EXISTE DEJA

            }
            else {
                // CREATION DU STOCK
                foreach($command_data as $key => $value) {
                    $stock[$key] = new StockVendeur;
                    $stock[$key]->produit = $value->produit_id;
                    $stock[$key]->vendeurs = $value->user_id;
                    $stock[$key]->quantite = $value->quantite_a_livrer;
                }
            }

            
            #CHANGEMENT DE STATUS

            $livraison_afrocash->state = true;

            foreach($command_data as $key => $value) {
                $value->state = true;
                $value->save();// MISE A JOUR DES INFOS DE LA COMMANDE
                $stock[$key]->save();// MISE A JOUR DU STOCK
                $serialStockAfrocash[$key]->save(); // AFFECTATION DES MATERIELS AU PDC
            }
            $livraison_afrocash->save(); // ENREGISTREMENT DES INFOS DE LIVRAISON

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    # ANNULATION DE COMMAND MATERIEL AFROCASH
    public function removeCommandAfrocash() {
        try {
            $validation = request()->validate([
                'id_commande'   =>  'required|string'
            ]);

            $id_commande = Crypt::decryptString(request()->id_commande);
            $command_afrocash = CommandAfrocash::where('id_commande',$id_commande)
                ->get();            

            if($command_afrocash->first()->state) {
                throw new AppException("Commande deja confirmee!");
            }

            // VERIFIER SI LA COMMANDE N'EST PAS DEJA CONFIRME
            
            $livraison = $command_afrocash->first()->livraison()->first();

            $livraison->remove_state = true;

            foreach($command_afrocash as $value) {
                $value->remove_state = true;
                $value->save();
            }
            $livraison->save();

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
