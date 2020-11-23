<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CommandAfrocash;
use App\Articles;
use App\Kits;
use Illuminate\Support\Facades\Crypt;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


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

    public function confirmCommandAfrocash() {
        try {

            return response()
                ->json(request());
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
