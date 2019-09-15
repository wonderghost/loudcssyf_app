<?php
namespace App\Http\Controllers;


//
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Exceptions\Handler;

use Illuminate\Http\Request;
use App\Http\Requests\DepotRequest;
use App\Http\Requests\MaterialRequest;
use App\Http\Requests\RavitaillementRequest;
use App\Http\Requests\MaterialEditRequest;


use App\User;
use App\Depots;
use App\Produits;
use App\Exemplaire;
use App\StockPrime;
use App\RavitaillementDepot;
use App\RavitaillementVendeur;
use App\Agence;
use App\Stock;
use App\StockVendeur;
use App\CgaAccount;
use App\RexAccount;
use App\CommandMaterial;
use App\RapportVente;
use App\Compense;
use App\CommandProduit;
use App\Livraison;
//
use App\Traits\Similarity;
use App\Traits\Livraisons;
//
use App\Exceptions\CommandStatus;

class LogistiqueController extends Controller
{
    //
    use Similarity;
    use Livraisons;

    public function makeDepot() {
    	$depots = Depots::all();
      $gestDepot = User::where('type','gdepot')->get();
    	return view('logistique.add-depot')->withDepots($depots)->withUserdepot($gestDepot);
    }

    public function addDepot(DepotRequest $request) {
    	$depot = new Depots;
    	$depot->localisation = $request->input('localisation');
      $depot->vendeurs = $request->input('vendeurs');
      if(Depots::where('vendeurs',$depot->vendeurs)->first()) {
        // CE VENDEURS EXISTE DEJA POUR UN DEPOT
        return redirect('/admin/add-depot')->with('_errors',"Utilisateur deja associe a un depot existant!");
      }
    	$depot->save();
    	return redirect('/admin/add-depot')->with('success',"Nouveau depot ajouté!");
    }

    // ajout d'un material
    public function addMaterial(MaterialRequest $request) {
            $produit = new Produits;
            do {
                $produit->reference = 'LMAT-'.mt_rand(100000,999999);
            } while ($this->isExisteMaterial($produit->reference));
            $produit->libelle = $request->input('libelle');
            $produit->prix_vente=$request->input('prix_unitaire');
            $quantite = $request->input('quantite');
            $produit->prix_initial = $request->input('prix_initial');
            $produit->marge = $request->input('marge');

        if($request->input('with_serial')) {
            // materiel avec numero de serie

            session([
                'quantite'=> $quantite,
                'produit'=>$produit,
                'depot'=>$request->input('depot')
        ]);

            return redirect('/admin/add-material/complete-registration');
        }
        // verifier si le produit existe
        if($__temp = $this->isExisteMaterialName($produit->libelle)) {
            // le produit existe deja
            $produit = $__temp;
        } else {
            $produit->save();
        }

        $stockCentral = new StockPrime;
        $entreeDepot = new RavitaillementDepot;
        $entreeDepot->produit = $produit->reference;
        $entreeDepot->depot = $request->input('depot');
        $entreeDepot->quantite = $request->input('quantite');
        //
        if($prod = $this->isInStock($produit->reference,$request->input('depot'))) {
            // le produit existe deja en stock
           $__quantite = $prod->quantite;
           $__quantite+= $request->input('quantite');
           StockPrime::select()->where('produit',$produit->reference)->where('depot',$request->input('depot'))->update([
            'quantite'=>$__quantite
            ]);
        } else {
            // le produit n'est pas stock
            $stockCentral->produit = $produit->reference;
            $stockCentral->depot = $request->input('depot');
            $stockCentral->quantite = $request->input('quantite');
            $stockCentral->save();
        }

        $entreeDepot->save();
        return redirect('/admin/add-depot')->with('success',"Enregistrement reussi!");
    }

    public function completeRegistration() {
        if(!session('produit')) {
            return redirect('/admin/add-depot')->with('_errors',"Enregistrement incomplete, Ressayez !");
        }
        return view('logistique.complete-registration');
    }

    public function completRegistrationFinal(Request $request) {
        $produit = session('produit');



        // ENREGISTREMENT DANS PRODUITS S'IL N'EXISTE PAS
        if(!$_temp = $this->isExisteMaterialName($produit->libelle)) {
            // Le materiel n'existe pas
            $produit->save();
        } else {
            // Le materiel existe
            $produit = $_temp;
        }
        // ENREGISTREMENT DES SERIAL NUMBER
        for($i=0;$i<session('quantite');$i++) {

            DB::table('exemplaire')->insert(
                [
                    'serial_number' => $request->input("serial-number-".($i+1)),
                    'produit' => $produit->reference
                ]
            );
            DB::table('stock_central')->insert(
                [
                    'exemplaire' => $request->input("serial-number-".($i+1)),
                    'depot' => session('depot')
                ]
            );
        }
        // ENREGISTREMENT DANS LE STOCK PRIME
        $entreeDepot = new RavitaillementDepot;
        $entreeDepot->produit = $produit->reference;
        $entreeDepot->depot = session('depot');
        $entreeDepot->quantite = session('quantite');

        if($prod = $this->isInStock($produit->reference,session('depot'))) {
            //LE PRODUIT EST DEJA PRESENT ON AUGMENTE LA QUANTITE
            $_quantite = $prod->quantite;
            $_quantite += session('quantite');
            StockPrime::select()->where('produit',$produit->reference)->where('depot',session('depot'))->update([
                'quantite'=> $_quantite
            ]);

        } else {
            // LE PRODUIT N'EST PAS PRESENT ON L'AJOUTE
            $stockPrime = new StockPrime;
            $stockPrime->produit = $produit->reference;
            $stockPrime->depot = session('depot');
            $stockPrime->quantite = session('quantite');
            $stockPrime->save();
        }
        $entreeDepot->save();
        session()->forget(['produit','quantite','depot']);
        return response()->json('success');
        // return redirect('/admin/add-depot')->with('success',"Enregistrement reussi!");
    }




    public function findMaterial(Request $request) {
        if($temp = $this->isExisteMaterialName($request->input('wordSearch'))) {
            return response()->json($temp);
        }
        return response()->json('fail');
    }




    public function abortRegistration(Request $request) {
        session()->forget(['produit','quantite','depot']);
        return response()->json('done');
    }
    // LISTE DES MATERIELS

    public function listMaterial() {
        $depots = Depots::all();
        return view('logistique.list-material')->withDepots($depots);
    }

    public function getListMaterial(Request $request) {
        $produit = [];

        $all = [];
        if($request->input('ref') !== 'all') {
            $stockDe = StockPrime::select()->where('depot',$request->input('ref'))->get();

                foreach($stockDe as $key => $value) {
                    $produit[$key] = Produits::select()->where('reference',$value->produit)->first();
                }

            // return response()->json($produit);
        } else {
            $produit = Produits::all();
        }

        if($produit) {

            foreach($produit as $key => $values) {
                if($request->input('ref') == 'all') {
                    $quantite = StockPrime::select()->where('produit',$values->reference)->sum('quantite');
                } else {
                    $quantite = StockPrime::select()->where('produit',$values->reference)->where('depot',$request->input('ref'))->sum('quantite');
                }
                $all[$key] = $this->organize($values,$quantite);
            }
        }
        // $all = $this->organize($produit)

        return response()->json($all);
    }

    // historique de ravitaillement du depot
    public function historyDepot() {
        $depot = Depots::all();
        return view('logistique.history-depot')->withDepots($depot);
    }

    //
    public function getHistoryDepot(Request $request) {
        $all = [];
        if($request->input('ref') == 'all'){
            $historique = RavitaillementDepot::select()->orderBy('created_at','desc')->get();
            foreach($historique as $key => $values) {
                $produit = Produits::select()->where('reference',$values->produit)->first();
                $all[$key]= $this->organizeCommand($values,$produit->libelle);
            }
        }

        return response()->json($all);
    }
// RAVITAILLEMENT VENDEUR
  public function addStock($commande) {

      if(!$this->isExistCommandOnly($commande)) {
          // LA COMMANDE N'EXISTE PAS
          return redirect('/user/commandes')->with("_errors","Ressayez Ulterieurement!");
      }
      // recuperer le vendeurs qui a emis la commande
      $user = CommandMaterial::where('id_commande',$commande)->first()->vendeurs()->first();

      $this->CommandChangeStatus($commande,$user->username);
      if($this->changeCommandStatusGlobale($commande)) {
        return redirect('/user/commandes');
      };
      $agence = Agence::where('reference',$user->agence)->first();
      $materiel = Produits::all();
      $depots = Depots::all();
      return view('logistique.add-ravitaillement')->withUsers($user)->withAgences($agence)
                        ->withMateriel($materiel)
                        ->withDepots($depots)
                        ->withCommande($commande);
    }

    // TRAITEMENT DE LA REQUETE DE COMMANDE , ENVOI DU RAVITAILLEMENT
    public function makeAddStock(RavitaillementRequest $request,$commande) {

        $msg = '';
        // VERIFIER SI UNE COMMANDE EXISTE AU NOM DU VENDEUR
        // dd($request);
        // dump($request);
        // die();
        if(!$this->isExisteCommandeForVendeur($request->input('vendeur'),$request->input('produit'),$commande)) {
            return back()->with('_errors',"Ce vendeur n'a emis aucune commande!");
        }
        $this->changeCommandStatusGlobale($commande);
            // VERIFIER SI LE MATERIEL EXISTE
        if($this->isExisteMaterial($request->input('produit'))) {
            // VERIFIER SI LE MATERIEL EXISTE DANS LE DEPOT CHOISI
            if($temp = $this->isInStock($request->input('produit'),$request->input('depot'))) {
                // VERIFIER SI LA QUANTITE EST DISPONIBLE
                if($temp->quantite >= $request->input('quantite')) {
                    // la quantite est disponible
                    // ################333
                    // Verifier si le  ravitaillement est possible
                    if($this->isRavitaillementPossible($commande,$request)) {
                      // LE RAVITAILLEMENT EST POSSIBLE
                    // VERIFIEZ SI'IL POSSEDE UN S/N
                    if($this->withSerialNumber($request->input('produit'))) {
                        // =============REDIRECTION VERS LA PAGE DE SAISI DES NUMEROS DE  SERIRES
                        session([
                            'produit' => $request->input('produit'),
                            'depot' => $request->input('depot'),
                            'quantite' => $request->input('quantite'),
                            'vendeur' => $request->input('vendeur'),
                            'commande'  =>  $commande,
                            'compense_quantite' =>  $request->input('compense')
                        ]);
                        return redirect("/user/ravitailler/$commande/complete-transfert");

                    } else {
                        // SANS S/N

                        // CALCUL DE LA NOUVELLE QUANTITE RESTANTE
                        $_quantite = StockPrime::select()->where('produit',$request->input('produit'))
                                                          ->where('depot',$request->input('depot'))->first()->quantite - $request->input('quantite');
                        StockPrime::select()->where('produit',$request->input('produit'))->where('depot',$request->input('depot'))->update([
                            'quantite'=> $_quantite
                        ]);

                        $stockVendeurs = new StockVendeur;
                        $stockVendeurs->produit = $request->input('produit');
                        $stockVendeurs->vendeurs = $request->input('vendeur');
                        $stockVendeurs->quantite = $request->input('quantite');

                        $ravitaillementVendeurs = new RavitaillementVendeur;
                        $livraison = new Livraison;
                        $ravitaillementVendeurs->id_ravitaillement  = "RA-".time();
                        $livraison->ravitaillement =  $ravitaillementVendeurs->id_ravitaillement;
                        $ravitaillementVendeurs->vendeurs = $request->input('vendeur');
                        $ravitaillementVendeurs->commands = $commande;

                        if($tmp = $this->vendeurHasStock($request->input('vendeur'),$request->input('produit'))) {
                            $laQuantite = $tmp->quantite + $request->input('quantite');
                            StockVendeur::select()->where('vendeurs',$request->input('vendeur'))->where('produit',$request->input('produit'))->update([
                                'quantite' => $laQuantite
                            ]);
                        } else {

                            $stockVendeurs->save();

                        }

                        $ravitaillementVendeurs->save();
                        // ENREGISTREMENT DE LA COMPENSE
                        if($request->input('compense') > 0) {
                            $compense = new Compense;
                            $compense->vendeurs = $request->input('vendeur');
                            $compense->materiel = $request->input('produit');
                            $compense->quantite = $request->input('compense');
                            $compense->type = 'debit';

                            $compense->save();
                        }

                        // ENREGISTREMENT DANS LA TABLE DE LIVRAISON

                        $livraison->produits = $request->input('produit');

                        $livraison->quantite = $request->input('quantite');
                        $livraison->depot = $request->input('depot');
                        $livraison->code_livraison = Str::random(10);;
                        $livraison->save();
                        // ---===
                        // verification du changement de status pour chaque produit de la commande
                        $this->CommandChangeStatus(session('commande'),session('vendeur')); //changement par produit pour la commande
                        $this->changeCommandStatusGlobale(session('commande')); //on confirme la commande globalement
                        return redirect('user/commandes')->with('success',"Ravitaillement reussi!");

                    }
                    // ici
                  } else {
                    $this->CommandChangeStatus(session('commande'),session('vendeur'));
                    $__produit = Produits::where("reference",$request->input('produit'))->first();
                    return back()->with("_errors","Quantite `$__produit->libelle` deja saisi!");
                  }
                } else {
                    // la quantite est indisponible
                    return back()->with('_errors',"Quantite indisponible");
                }
            } else {
                // indisponible dans le stock specifie
                return back()->with('_errors',"Materiel indisponible dans le stock choisi");
            }

        } else {
            // le materiel n'existe pas dans le systeme
            return back()->with('_errors',"Material inexistant dans le systeme");
        }
    }

    public function makeObject($request,$command) {
        // CONSTRUCTION DES OBJECTS
        $stockVendeurs = new StockVendeur;
        $stockVendeurs->produit = $request->input('produit');
        $stockVendeurs->vendeurs = $request->input('vendeur');
        $stockVendeurs->quantite = $request->input('quantite');

        $ravitaillementVendeurs = new RavitaillementVendeur;
        $ravitaillementVendeurs->id_ravitaillement  = "RA-".time();
        $ravitaillementVendeurs->vendeurs = $request->input('vendeur');
        $ravitaillementVendeurs->commands = $command;
        return ['vendeur'=>$stockVendeurs,'historique'=>$ravitaillementVendeurs];
    }

    public function vendeurHasStock($vendeur,$ref) {
        $temp = StockVendeur::select()->where('vendeurs',$vendeur)->where('produit',$ref)->first();
        if($temp) {
            return $temp;
        }
        return false;
    }

    public function getVendeur() {
        return User::select()->where('type','v_da')->orWhere('type','v_standart')->get();
    }

    public function inventory() {
        $user = $this->getVendeur();
        $agence = [];
        foreach($user as $key => $values) {
            $agence[$key] = Agence::select()->where('reference',$values->agence)->first();
        }
        return view('logistique.inventory')->withUsers($user)->withAgence($agence);
    }

    public function getListMaterialByVendeurs(Request $request) {
        $produit = [];
        $stock = [];

        $all = [];
        if($request->input('ref') == "all") {
            //
            $produit = Produits::select()->get();
            // var_dump($produit);
            foreach($produit as $key => $values ) {
                // $stock [$key]=
                $quantite = StockVendeur::select()->where('produit',$values->reference)->sum('quantite');
                $all[$key] = $this->organize($values,$quantite);
            }
            // return response()->json($all);



        } else {
             $stock = StockVendeur::select()->where('vendeurs',$request->input('ref'))->get();
             //
             foreach($stock as $key => $value) {
                    $produit[$key] = Produits::select()->where('reference',$value->produit)->first();
                }
                //
             if($produit) {

                foreach($produit as $key => $values) {
                    $quantite = StockVendeur::select()->where('produit',$values->reference)->where('vendeurs',$request->input('ref'))->first()->quantite;
                    $all[$key] = $this->organize($values,$quantite);
                }
            }
            //

        }
        return response()->json($all);

    }

    public function inventoryVendeur() {
        // dd(Auth::user()->username);
        $soldeCga = CgaAccount::select()->where('vendeur',Auth::user()->username)->first();
        $soldeRex = RexAccount::select()->where('numero',Auth::user()->rex)->first();
        // dd($soldeCga);
        return view('logistique.my-inventory')->withSolde($soldeCga)
                                                ->withRex($soldeRex);
    }

    public function historyRavitaillement() {
        return view('logistique.my-stock-history');
    }
    public function getHistoryRavitaillement(Request $request) {
         $all = [];
        if($request->input('ref')){
            $historique = RavitaillementVendeur::select()->where('vendeurs',$request->input('ref'))->orderBy('created_at','desc')->get();
            foreach($historique as $key => $values) {
                $produit = Produits::select()->where('reference',$values->produit)->first();
                $all[$key]= $this->organizeCommand($values,$produit->libelle);
            }
        }
        return response()->json($all);
    }

    //

    public function editMaterial($reference) {
        $material = Produits::select()->where('reference',$reference)->first();
        return view('logistique.edit-material')->withMaterial($material);
    }
    //
    public function makeEditMaterial(MaterialEditRequest $request) {
        Produits::select()->where('reference',$request->input('reference'))->update([
            'libelle' => $request->input('libelle'),
            'prix_initial' => $request->input('prix_initial'),
            'prix_vente' => $request->input('prix_unitaire'),
            'marge' => $request->input('marge')
        ]);
        return redirect("/admin/edit-material/".$request->input('reference'))->with('success',"Modification reussie!");
    }

    //

    public function getMaterialByDepot(Request $request) {
        $stock = StockPrime::select()->where('produit',$request->input('ref'))->get();
        return response()->json($stock);
    }
    //

    public function completeTransfert() {
        if(!session('vendeur')) {
            return back()->with('_errors',"Operation annulée, Ressayez!");
        }
        return view('logistique.complete-transfert');
    }

    public function completeTransfertFinal(Request $request) {
        // VERIFIER SI LES NUMEROS DE SERIES EXISTE
        $_serial = [];
        $__serial = [];
        for($i=1;$i <= session('quantite') ; $i++ ) {
            array_push($_serial,$request->input("serial-number-".$i));
        }
        // VERIFIEZ SI S/N EXISTE DANS LE DEPOT
        foreach($_serial as $values) {
            if(!$this->isSNInDepot($values,session('depot'))) {
                return back()->with('_errors',"Ce numero n'existe pas dans le depot choisi ".$values);
            }
        }
        // VERIFIEZ SI S/N N'EST PAS ATTRIBUEZ
        foreach($_serial as $values) {
            if(!$this->isSNToUser($values,session('produit'))) {
                return back()->with('_errors',"Ce numero est deja attribuer ".$values);
            }
        }

        //
        $stockVendeurs = new StockVendeur;
        $stockVendeurs->produit = session('produit');
        $stockVendeurs->vendeurs = session('vendeur');
        $stockVendeurs->quantite = session('quantite');

        $ravitaillementVendeurs = new RavitaillementVendeur;
        $livraison  = new Livraison;

        $ravitaillementVendeurs->id_ravitaillement  = "RA-".time();
        $ravitaillementVendeurs->vendeurs = session('vendeur');
        $ravitaillementVendeurs->commands = session('commande');


        $livraison->ravitaillement  = $ravitaillementVendeurs->id_ravitaillement;
        $livraison->produits  = session('produit');
        $livraison->quantite  = session('quantite');
        $livraison->depot   = session('depot');
        $livraison->code_livraison  = Str::random(10);

        // ATTRIBUTION DES NUMEROS DE SERIES

        foreach($_serial as $values) {
            Exemplaire::select()->where('serial_number',$values)->update([
                'vendeurs' => session('vendeur')
            ]);
        }

        // // Modification de la quantite dans le depot

        $_quantite = StockPrime::select()->where('produit',session('produit'))->where('depot',session('depot'))->first()->quantite - session('quantite');
        StockPrime::select()->where('produit',session('produit'))->where('depot',session('depot'))->update([
            'quantite'=> $_quantite
        ]);


        if($tmp = $this->vendeurHasStock(session('vendeur'),session('produit'))) {
            $laQuantite = $tmp->quantite + session('quantite');
            StockVendeur::select()->where('vendeurs',session('vendeur'))->where('produit',session('produit'))->update([
                'quantite' => $laQuantite
            ]);

        } else {
            $stockVendeurs->save();
        }

        $ravitaillementVendeurs->save();
        $livraison->save();
        if(session('compense') > 0) {
            // ENREGISTREMENT DE LA COMPENSE
            $compense = new Compense;
            $compense->vendeurs = session('vendeur');
            $compense->materiel = session('produit');
            $compense->type = 'debit';
            $compense->quantite = session('compense_quantite');
            $compense->save();
        }
        // VERIFICATION POUR LE CHANGEMENT D'ETAT DE LA COMMANDE
        $this->CommandChangeStatus (session('commande'),session('vendeur'));
        // die();

        session()->forget(['produit','quantite','depot','vendeur','commande']);
        return redirect('user/commandes')->with('success',"Ravitaillement reussi!");
    }

    public function abortTransfert() {
        session()->forget(['produit','quantite','depot','vendeur']);
        return response()->json('done');
    }


    public function allCommandes() {
        return view('logistique.list-commandes');
    }

    public function getAllCommandes(Request $request) {
        $commands= CommandMaterial::all();

        foreach($commands as $key => $values) {
            // $produit = Produits::select()->where('reference',$values->produit)->first();
            $vendeurs = User::where('username',$values->vendeurs)->first();
            if($vendeurs->type == "v_da") {
              $agence = Agence::where('reference',$vendeurs->agence)->first()->societe;
            } else {
              $agence = $vendeurs->localisation;
            }

            $date = new Carbon($values->created_at);

            $command_produit = CommandProduit::where([
              'commande'  =>  $values->id_commande
              ])->first();

              $migration = RapportVente::where('vendeurs',$values->vendeurs)->sum('quantite_migration');
          		$compense = Compense::where([
          			'vendeurs'	=>	Auth::user()->username,
          			'materiel'	=>	Produits::where('libelle','Parabole')->first()->reference
          			])->sum('quantite');

              $parabole_a_livrer  = $command_produit->parabole_a_livrer - ($migration + $compense);

            $all [$key] = [
                'item' => 'Kit complet',
                'quantite' => $command_produit->quantite_commande,
                'numero_recu' => $values->numero_versement,
                'status' =>  ($values->status == 'unconfirmed') ? 'en attente' : 'confirmer',
                'id' => $values->id,
                'vendeurs'  =>  $values->vendeurs.' ( '.$agence.' )',
                'date'  =>  $date->toFormattedDateString().' | '.$date->toTimeString(),
                'image' =>  $values->image,
                'parabole_a_livrer' =>  $parabole_a_livrer,
                'link'  =>  url('user/ravitailler',[$values->id_commande])
                    ];
        }
        return response()->json($all);
    }

    public function getParaboleDu(Request $request) {

        $migration = RapportVente::where('vendeurs',$request->input('ref'))->sum('quantite_migration');

        $compense   = Compense::where([
          'type'=>'debit',
          'vendeurs'  =>  $request->input('ref')
          ])->get()->sum('quantite');
          // quantite de parabole du
        $parabole_du = $migration - $compense;
        return response()->json($parabole_du);
        // return response()->json([
        //   'parabole_du' =>  $parabole_du,
        //   'restant_pour_ravitaillement' =>  $this->getParaboleRestantPourRavitaillement($vendeur,$command,$material);
        // ]);
    }

    public function getRestantPourRavitaillement(Request $request) {
      // Quantite de parabole restant pour ravitaillement
      #quantite commander
      $quantiteCommande = CommandProduit::where([
        'commande'  =>  $request->input('command'),
        'produit' =>  $request->input('material')
      ])->first()->quantite_commande;

      #quantite deja envoyer
      $ravitaillements  = RavitaillementVendeur::select('id_ravitaillement')->where([
        'vendeurs'  =>  $request->input('vendeurs'),
        'commands'  =>  $request->input('command')
      ])->get();

      $quantiteEnvoyer = Livraison::whereIn('ravitaillement',$ravitaillements)->where('produits',$request->input('material'))->sum('quantite');

      $restantPourRavitaillement = $quantiteCommande - $quantiteEnvoyer ;
      return  response()->json($restantPourRavitaillement);
    }
    //  CONFIRMER UNE COMMANDE
    public function confirmCommand($idCommande) {
        if(CommandMaterial::where(['id'    =>  $idCommande,'status'    =>  'unconfirmed'])->first()) {
            CommandMaterial::where([
                'id'    =>  $idCommande,
                'status'    =>  'unconfirmed'
            ])->update([
                'status'    =>  'confirmed'
            ]);
        return response()->json('ok');
        }
        else {
            return response()->json('fail');
        }
    }


}
