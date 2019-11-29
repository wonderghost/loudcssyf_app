<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\ClientRequest;
use App\Traits\Similarity;
use App\Traits\Abonnements;
use App\Traits\Recrutement;
use App\Traits\Cga;
use App\Client;
use App\Repertoire;
use App\Exemplaire;


class VendeurController extends Controller
{
    //
    use Cga;
    use Similarity;
    use Recrutement;
    use Abonnements;

    protected $materialPrice    =   0;

    public function __construct() {
        //
    }

    public function addClient() {
    	return view('simple-users.add-client');
    }

    public function makeAddClient(ClientRequest $request) {
        if($client = $this->isExistClientInSystem($request->input('email'))) {
            if($rep = $this->isExistClient($client->email,Auth::user()->username)) {
                // dd($client);
                return redirect('/user/add-client')->with('_errors',"Cet Client Existe deja!");

            } else {
                $repertoire = new Repertoire;
                $repertoire->client = $client->email;
                $repertoire->vendeur = Auth::user()->username;
                $repertoire->save();
            }
        } else {
            $client = new Client;
            $client->nom = $request->input('nom');
            $client->prenom = $request->input('prenom');
            $client->email = $request->input('email');
            $client->phone = $request->input('phone');
            $client->adresse = $request->input('adresse');

            $repertoire = new Repertoire;
            $repertoire->client = $request->input('email');
            $repertoire->vendeur = Auth::user()->username;
            // dd($repertoire);
            $client->save();
            $repertoire->save();
        }
        	return redirect('/user/add-client')->with('success',"Nouveau client ajouté!");

    	// dd($client);
    }

    public function listClient() {

        return view('simple-users.list-client');
    }

    public function getListClient(Request $request) {
        $all = Client::select()->whereIn('email',Repertoire::select('client')->where('vendeur',Auth::user()->username))->get();
        return response()->json($all);
    }

    public function allVentes() {
        return view('ventes.toutes-ventes');
    }

    public function abonnement() {
        return view('ventes.abonnement');
    }

    // liste des numero de serie de materiel par vendeur
    public function SerialForVendeur(Request $request) {
      $serials = Exemplaire::where('vendeurs',$request->input('ref'))->get();
      $all =[];
      foreach ($serials as $key => $value) {
        $all[$key]= [
          'serial'  =>  $value->serial_number,
          'article' =>  'Terminal Hd z4',
          'vendeur' =>  $value->vendeurs,
          'status'  =>  $value->status
        ];
      }
      return response()->json($all);
    }
}
