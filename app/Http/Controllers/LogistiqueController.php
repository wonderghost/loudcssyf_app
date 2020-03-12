<?php
namespace App\Http\Controllers;


//
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
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
use App\Afrocash;
//
use App\Traits\Similarity;
use App\Traits\Livraisons;
//
use App\Exceptions\CommandStatus;
use App\Exceptions\AppException;


class LogistiqueController extends Controller
{
    //
    use Similarity;
    use Livraisons;

    // liste des numeros de serie des materiels
    public function ListSerialNumber(Request $request) {
      $serials = Exemplaire::select()->orderBy('serial_number','asc')->paginate(10);
      $all = [];
      foreach ($serials as $key => $element) {
        $all[$key] = [
          'serial_number' =>  $element->serial_number,
          'vendeurs'  =>   $element->vendeurs()->first() ? $element->vendeurs()->first()->localisation : 'non attribuer' ,
          'status'  =>  $element->status
        ];

      }
      return response()->json([
        'all' => $all,
        'serial' => $serials]);
    }
    // ravitailler un depot
    public function ravitaillerDepot() {
      return view('logistique.ravitailler-depot');
    }

    // Ravitaillement des depot par le responsable logistique

    public function sendRavitaillementDepot(Request $request) {

      $validationRules = $request->validate([
        'produit' =>  'required|string|exists:produits,reference',
        'depot' =>  'required|string|exists:depots,localisation',
        'quantite'  =>  'required|min:1',
        'serials.*' =>  'distinct|unique:exemplaire,serial_number'
      ],[
        'distinct'  =>  'Champs :attribute a ete duplique',
        'required'  =>  'Champs :attribute obligatoire',
        'unique'  =>  'Champs :attribute existant'
      ]);

      try {
        // verifier si la quantite existe dans le depot central
        if($this->isQuantiteValidInDepotCentral($request->input('produit'),$request->input('quantite'))) {
          // verifier si le serial_number existe
          if($this->isWithSerialNumber($request->input('produit'))) {

            session([
              'quantite'  =>  $request->input('quantite'),
              'produit' =>  $request->input('produit'),
              'depot' =>  $request->input('depot')
            ]);
            #@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@22222
            $produit = Produits::find(session('produit'));

            // ENREGISTREMENT DES SERIAL NUMBER
            for($i=0;$i<session('quantite');$i++) {

                DB::table('exemplaire')->insert(
                    [
                        'serial_number' => $request->input("serials")[$i],
                        'produit' => $produit->reference
                    ]
                );
                DB::table('stock_central')->insert(
                    [
                        'exemplaire' => $request->input("serials")[$i],
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
            // modification dans le depot central
            $newQuantite = $produit->quantite_centrale - session('quantite');
            $produit->quantite_centrale = $newQuantite;
            // ENVOI DE LA NOTIFICATION
            $_depot = Depots::find(session('depot'));

            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".session('depot'),Auth::user()->username);
            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".session('depot'),$_depot->vendeurs);
            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".session('depot'),User::where("type",'admin')->first()->username);
            $entreeDepot->save();
            $produit->save();
            session()->forget(['produit','quantite','depot']);

            return response()->json('done');



            #@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

          } else {
            // le numero de serie n'existe pas

            $produit = Produits::find($request->input('produit'));


            if($prod = $this->isInStock($produit->reference,$request->input('depot'))) {
              // existe deja augmenter la quantite
              $_quantite = $prod->quantite;
              $_quantite += $request->input("quantite");
              StockPrime::select()->where('produit',$produit->reference)->where('depot',$request->input('depot'))->update([
                  'quantite'=> $_quantite
              ]);

            } else {
              // n'existe pas on l'ajoute
              $stockPrime = new StockPrime;
              $stockPrime->produit = $produit->reference;
              $stockPrime->depot = $request->input('depot');
              $stockPrime->quantite = $request->input('quantite');
              $stockPrime->save();
            }
            // $stockDepot->
            $entreeDepot = new RavitaillementDepot;
            $entreeDepot->produit = $produit->reference;
            $entreeDepot->depot = $request->input('depot');
            $entreeDepot->quantite = $request->input("quantite");

            $newQuantite = $produit->quantite_centrale - $request->input('quantite');
            $produit->quantite_centrale = $newQuantite;
            //
         // ENVOI DE LA NOTIFICATION
            $_depot = Depots::find($request->input('depot'));
            $this->sendNotification("Ravitaillement Depot!","Ravitaillement effectue au compte de : ".$request->input('depot'),User::where('type','admin')->first()->username);
            $this->sendNotification("Ravitaillement Depot!","Ravitaillement effectue au compte de : ".$request->input('depot'),Auth::user()->username);
            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".$request->input('depot'),$_depot->vendeurs);
            $entreeDepot->save();
            $produit->save();
            // return redirect("/user/list-material")->withSuccess("Success!");
            return response()
              ->json('done');
          }
        } else {
          throw new AppException("Quantite indisponible");
        }

      } catch (AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }

    }
    // recuperer la quantite de materiel disponible

    public function getMaterialDispo(Request $request) {
      $material = Produits::find($request->input("ref"));
      if($material) {
        return response()->json([
          'quantite_disponible' =>  $material->quantite_centrale
        ]);
      }
      return response()->json('fail');
    }

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

    // ajout d'un material dans le depot central
    public function addMaterial(MaterialRequest $request) {
      $produit = new Produits;
      // verifier l'unicite de la reference
      // dd($request);
      do {
          $produit->reference = 'LMAT-'.mt_rand(100000,999999);
      } while ($this->isExisteMaterial($produit->reference));
      $produit->libelle = $request->input('libelle');
      $produit->prix_vente=$request->input('prix_unitaire');
      $produit->quantite_centrale = $request->input("quantite");
      $produit->prix_initial = $request->input('prix_initial');
      $produit->marge = $request->input('marge');
      if($request->input('with_serial')) {
        $produit->with_serial = $request->input('with_serial');
      } else {
        $produit->with_serial = 0;
      }
      if($this->isExisteMaterialName($request->input('libelle'))) {
        // le materiel existe deja donc augmenter juste la quantite
        $newQuantite  = Produits::where('libelle',$request->input('libelle'))->first()->quantite_centrale + $request->input('quantite');
        Produits::where('libelle',$request->input('libelle'))->update([
          'quantite_centrale' =>  $newQuantite
        ]);
      } else  {
        // le materiel n'existe pas donc l'ajouter
        $produit->save();
      }
      return redirect('/admin/add-depot')->with('success',"Enregistrement reussi!");
    }

    public function completeRegistration() {
        if(!session('produit')) {
            return redirect('/admin/add-depot')->with('_errors',"Enregistrement incomplete, Ressayez !");
        }
        return view('logistique.complete-registration');
    }

    public function completRegistrationFinal(Request $request) {

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
      // recuperer le vendeurs qui a emis la commande
    $user = CommandMaterial::where('id_commande',$commande)->first()->vendeurs()->first();
    // dump($this->CommandChangeStatus($commande,$user->username));
    $this->CommandChangeStatus($commande,$user->username);
    if($this->changeCommandStatusGlobale($commande)) {
      return redirect('/user/commandes');
    };

    return view('logistique.add-ravitaillement')->withCommande($commande);
    }

    // TRAITEMENT DE LA REQUETE DE COMMANDE , ENVOI DU RAVITAILLEMENT
    public function makeAddStock(RavitaillementRequest $request,$commande) {
      try {
        // existence de la commande pour le vendeur selectionne

        if($this->isExisteCommandeForVendeur(
          $request->input('vendeur'),
          $commande)) {
            if($this->isDisponibleInDepot($request->input('depot'),$request->input('produit'),$request->input('quantite'))) {
              // disponibilite de la quantite dans le depot central
              if($this->isRavitaillementPossible($commande,$request)) {
                // verifier si le ravitaillement est possible pour ce vendeur
                // ##@+++++
                $ravitaillementVendeur = new RavitaillementVendeur;
                $ravitaillementVendeur->id_ravitaillement = 'RAV-'.time();
                $ravitaillementVendeur->vendeurs = $request->input('vendeur');
                $ravitaillementVendeur->commands  = $commande;

                // creation de la livraison
                $livraison = new Livraison ;
                $livraison->ravitaillement = $ravitaillementVendeur->id_ravitaillement;
                $livraison->produits = $request->input('produit');
                $livraison->depot = $request->input('depot');
                $livraison->quantite = $request->input('quantite');
                do {
                  $livraison->code_livraison  = Str::random(6);
                } while ($this->existCode($livraison->code_livraison));

                //debit dans le depot choisi

                $newQuantite = StockPrime::where([
                  'produit'  =>  $request->input('produit'),
                  'depot' =>  $request->input('depot')
                ])->first()->quantite - $request->input('quantite');

                StockPrime::where([
                  'produit' =>  $request->input('produit'),
                  'depot' =>  $request->input('depot')
                ])->update([
                  'quantite'  =>  $newQuantite
                ]);

                if($this->vendeurHasStock($request->input('vendeur'),$request->input('produit'))) {
                  // verifier si le produit a ete enregistre au moins une fois
                  $quantiteVendeur = StockVendeur::where([
                    'vendeurs'  =>  $request->input('vendeur'),
                    'produit' =>  $request->input('produit')
                  ])->first()->quantite + $request->input('quantite');
                  //
                  StockVendeur::where([
                    'vendeurs'  =>  $request->input('vendeur'),
                    'produit' =>  $request->input('produit')
                  ])->update([
                    'quantite'  =>  $quantiteVendeur
                  ]);

                } else {
                  // enregistrer pour la premiere fois
                  $stockVendeur = new StockVendeur;
                  $stockVendeur->produit = $request->input('produit');
                  $stockVendeur->vendeurs = $request->input('vendeur');
                  $stockVendeur->quantite = $request->input('quantite');
                  $stockVendeur->save();
                }

                $ravitaillementVendeur->save();
                $livraison->save();
                // ENREGISTREMENT DE LA NOTIFICATION
                $this->sendNotification("Ravitaillement Materiel","Ravitaillement materiel effectue au compte de :".User::where('username',$request->input('vendeur'))->first()->localisation,User::where('type','admin')->first()->username);
      					$this->sendNotification("Ravitaillement Materiel" ,"Vous avez effectue un ravitaillement au compte de ".User::where("username",$request->input('vendeur'))->first()->localisation,Auth::user()->username);
      					$this->sendNotification("Ravitaillement Materiel" ,"Votre commande materiel a ete confirme , rendez vous dans le depot : ".$request->input('depot'),$request->input('vendeur'));
                $this->sendNotification("Livraison Materiel" ,"Vous avez une livraison a effectue au compte de : ".User::where('username',$request->input('vendeur'))->first()->localisation , Depots::where("localisation",$request->input("depot"))->first()->vendeurs);
                return response()
                  ->json('done');
              } else {
                throw new AppException("Ravitaillement indisponible");
              }
            } else {
              throw new AppException("Quantite Indisponible!");
            }
        } else {
          throw new AppException("Commande Invalide!");
        }
      } catch (AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }

    }

    public function vendeurHasStock($vendeur,$ref) {
        $temp = StockVendeur::where([
          'vendeurs'  =>  $vendeur,
          'produit' =>  $ref
          ])->first();
        if($temp) {
            return $temp;
        }
        return false;
    }

    public function getVendeur() {
        return User::select()->where('type','v_da')->orWhere('type','v_standart')->get();
    }

    public function inventory() {
      return view('logistique.inventory');
    }
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    public function allVendeurs(User $u) {
      try {
        return response()
          ->json($u->whereIn('type',['v_da','v_standart'])
          ->orderBy('localisation','asc')
          ->get());
      } catch (AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }

    }
    public function getListMaterialByVendeurs(Request $request , Exemplaire $sn) {
      try {
        $serials = $sn->whereNotNull('vendeurs')->orderBy('serial_number','asc')->get();
        $all = [];
        foreach($serials as $key => $value) {
          $all[$key] = [
            'user_id' =>  $value->vendeurs,
            'numero_serie'  =>  $value->serial_number,
            'vendeurs'  => $value->vendeurs() ? $value->vendeurs()->localisation : 'undefined',
            'article' =>  $value->produit()->libelle,
            'status'  =>  $value->status,
            'origine' =>  $value->depot()->depot
          ];
        }
        return response()
          ->json($all);
      }catch (AppException $e) {
        header("Erreur ",true,422);
        die(json_encode($e->getMessage()));
      }
    }

    // materiels inventaire pour les vendeurs
    public function getAllMaterialByVendeurs (Request $request , StockVendeur $sv , Produits $p) {
      try {
        $result = $sv->select('produit')
          ->groupBy('produit')
          ->get();
          $all = $this->organizeAllMaterial($result,$request,$p,$sv , true);
          return response()
            ->json($all);
      } catch (AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }
    }
    // materiels inventaire global du reseau
    public function getAllMaterialForVendeurs(Request $request , StockVendeur $sv , Produits $p) {
      try {
        $result = $sv->select('produit')->groupBy('produit')->get();
        $all = $this->organizeAllMaterial($result,$request , $p , $sv);
        return response()
          ->json($all);
      } catch (AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }
    }

    public function organizeAllMaterial($data , Request $request , Produits $p , StockVendeur $sv , $vendeur = false) {
      $all = [];
      foreach($data as $key => $value) {
        $article = $p->where('reference',$value->produit)->first();
        if(!$vendeur) {
          $quantite = $sv->where('produit',$value->produit)->sum('quantite');
        } else {
          $quantite = $sv->where('produit',$value->produit)
            ->where('vendeurs',$request->user()->username)
            ->sum('quantite');
        }
        $all[$key] = [
          'article' => $article->libelle,
          'quantite'  =>  $quantite,
          'prix_initial' =>  $article->prix_initial,
          'prix_ttc'  =>  $article->prix_vente,
          'marge' =>  $article->marge,
          'ht'  =>  ceil($article->prix_vente/1.18),
          'tva' =>  '18%'
        ];
      }
      return $all;
    }
// @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    public function inventoryVendeur() {
      return view('logistique.my-inventory');
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


    public function getParaboleDu($vendeur) {

        $migration = RapportVente::where('vendeurs',$vendeur)->where('type','migration')->sum('quantite');
        $compense   = Compense::where([
          'type'=>'debit',
          'vendeurs'  =>  $vendeur
          ])->get()->sum('quantite');
          // quantite de parabole duDA-5648
        $parabole_du = $migration - $compense;
        return $parabole_du;
    }

    public function getRestantPourRavitaillement($commande , $materiel ,$vendeur ) {
      // Quantite de parabole restant pour ravitaillement
      #quantite commander
      $quantiteCommande = CommandProduit::where([
        'commande'  =>  $commande,
        'produit' =>  $materiel
      ])->first()->quantite_commande;

      #quantite deja envoyer
      $ravitaillements  = RavitaillementVendeur::select('id_ravitaillement')->where([
        'vendeurs'  =>  $vendeur,
        'commands'  =>  $commande
      ])->get();

      $quantiteEnvoyer = Livraison::whereIn('ravitaillement',$ravitaillements)->where('produits',$materiel)->sum('quantite');

      $restantPourRavitaillement = $quantiteCommande - $quantiteEnvoyer ;
      return $restantPourRavitaillement;
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
#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
// GET INFOS COMMANDES
  public function getInfosCommande($slug , CommandMaterial $c ,Produits $p) {
    try {
      $commande = $c->find($slug);
      $rv = [];
      foreach($commande->commandProduits()->get() as $key => $value) {
        $terminal = 0;
        $parabole = 0;
        if($value->produits()->first()->with_serial == 1){
          $terminal = $this->getRestantPourRavitaillement($value->commande,$value->produit,$commande->vendeurs);
        } else {
          $parabole = $this->getRestantPourRavitaillement($value->commande,$value->produit,$commande->vendeurs);
        }
      }
      $rv = [
        'terminal'  =>  $terminal,
        'parabole'  => $parabole
      ];
      $all = [
        'id'  =>  $commande->id_commande,
        'status'  =>  $commande->status,
        'vendeurs' => $commande->vendeurs,
        'vendeurs_localisation' =>  $commande->vendeurs()->first()->localisation,
        'parabole_du'  =>  $this->getParaboleDu($commande->vendeurs),
        'restant_ravit' =>  $rv,
        'materials' =>  $p->select(['reference','libelle'])->get()
      ];

      return response()
        ->json($all);
    } catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }

  public function depotList (Depots $d , Produits $p) {
    try {
      
      $depots = $d->all();
      $all = [];
      foreach($depots as $key => $value) {
        $stock = $value->stockMateriel()->get();

        $terminal_quantite = 0 ;
        $parabole_quantite = 0;
        foreach ($stock as $_value) {
          if($_value->produits()->first()->with_serial == 1) {
            $terminal_quantite = $_value->quantite;
          } else {
            $parabole_quantite = $_value->quantite;
          }
        }

        $all[$key] =[
          'localisation'  =>  $value->localisation,
          'terminal'  =>  $terminal_quantite,
          'parabole'  =>  $parabole_quantite,
        ];
      }

      return response()
        ->json($all);

    } catch (AppException $e) {
      header("Erreur!",true,422);
      die(json_encode($e->getMessage()));
    }
  }
// RECUPERATION DES SERIALS NUMBER NON ATTRIBUE AUX VENDEURS
  public function getSerialNumberForDepot(Exemplaire $sn , Stock $s) {
    try {
      $non_attribuer = $sn->select('serial_number')
        ->whereNull('vendeurs')
        ->get();
      $stock = $s->whereIn('exemplaire',$non_attribuer)->orderBy('exemplaire','asc')->get();

        $all = [];
        foreach($stock as $key => $value) {
          $all [$key] =[
            'numero_materiel'  =>   $value->exemplaire,
            'depot' =>  $value->depot,
            'article' =>  "Terminal",
            'origine' =>  $value->origine
          ];
        }
      return response()
        ->json($all);
    }
    catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }
  // @@@@@@ get material @@@@@@@@@///
  public function getMateriel(Produits $p) {
    try {
      return response()
        ->json($p->select(['reference','libelle','with_serial'])->get());
    } catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }
  // @@@ get Solde Afrocash Logistique
  public function getSoldeLogistique(Request $request) {
    try {
      return response()
        ->json($request->user()->afrocashAccount());
    }
    catch (AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }
}
