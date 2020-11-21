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
use App\CompenseMaterial;
use App\DeficientMaterial;

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

            // session([
            //   'quantite'  =>  $request->input('quantite'),
            //   'produit' =>  $request->input('produit'),
            //   'depot' =>  $request->input('depot')
            // ]);
            #@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@22222
            $produit = Produits::find(request()->produit);

            
            // ENREGISTREMENT DES SERIAL NUMBER
            $serialExemplaire = [];
            $stockCentral = [];

            foreach(request()->serials as $key => $value) {
              $serialExemplaire[$key] = new Exemplaire;
              $serialExemplaire[$key]->serial_number = $value;
              $serialExemplaire[$key]->produit = $produit->reference;
              $serialExemplaire[$key]->origine = true;

              $stockCentral[$key] = new Stock;
              $stockCentral[$key]->exemplaire = $value;
              $stockCentral[$key]->depot = request()->depot;


              $serialExemplaire[$key]->save();
              $stockCentral[$key]->save();
            }
            
            // ENREGISTREMENT DANS LE STOCK PRIME

            $entreeDepot = new RavitaillementDepot;
            $entreeDepot->produit = $produit->reference;
            $entreeDepot->depot = request()->depot;
            $entreeDepot->quantite = request()->quantite;

            

            if($prod = $this->isInStock($produit->reference,request()->depot)) {
                //LE PRODUIT EST DEJA PRESENT ON AUGMENTE LA QUANTITE
                $_quantite = $prod->quantite;
                $_quantite += request()->quantite;
                StockPrime::select()->where('produit',$produit->reference)->where('depot',request()->depot)
                  ->update([
                    'quantite'=> $_quantite
                ]);

            } else {
                // LE PRODUIT N'EST PAS PRESENT ON L'AJOUTE
                $stockPrime = new StockPrime;
                $stockPrime->produit = $produit->reference;
                $stockPrime->depot = request()->depot;
                $stockPrime->quantite = request()->quantite;
                $stockPrime->save();
            }
            // modification dans le depot central
            $newQuantite = $produit->quantite_centrale - request()->quantite;
            $produit->quantite_centrale = $newQuantite;
            // ENVOI DE LA NOTIFICATION
            $_depot = Depots::find(request()->depot);

            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".request()->depot,Auth::user()->username);
            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".request()->depot,$_depot->vendeurs);
            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".request()->depot,User::where("type",'admin')->first()->username);
            $entreeDepot->save();
            $produit->save();
            

            return response()->json('done');

            #@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

          } else {
            // le numero de serie n'existe pas

            $produit = Produits::find(request()->produit);


            if($prod = $this->isInStock($produit->reference,$request->input('depot'))) {
              // existe deja augmenter la quantite
              $_quantite = $prod->quantite;
              $_quantite += request()->quantite;
              StockPrime::select()->where('produit',$produit->reference)->where('depot',request()->depot)->update([
                  'quantite'=> $_quantite
              ]);

            } else {
              // n'existe pas on l'ajoute
              $stockPrime = new StockPrime;
              $stockPrime->produit = $produit->reference;
              $stockPrime->depot = request()->depot;
              $stockPrime->quantite = request()->quantite;
              $stockPrime->save();
            }
            // $stockDepot->
            $entreeDepot = new RavitaillementDepot;
            $entreeDepot->produit = $produit->reference;
            $entreeDepot->depot = request()->depot;
            $entreeDepot->quantite = request()->quantite;

            $newQuantite = $produit->quantite_centrale - request()->quantite;
            $produit->quantite_centrale = $newQuantite;
            //
         // ENVOI DE LA NOTIFICATION
            $_depot = Depots::find(request()->depot);
            $this->sendNotification("Ravitaillement Depot!","Ravitaillement effectue au compte de : ".request()->depot,User::where('type','admin')->first()->username);
            $this->sendNotification("Ravitaillement Depot!","Ravitaillement effectue au compte de : ".request()->depot,Auth::user()->username);
            $this->sendNotification("Ravitaillement Materiel","Ravitaillement effectue au compte de : ".request()->depot,$_depot->vendeurs);
            $entreeDepot->save();
            $produit->save();
            
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

    // ajout d'un material dans le depot central
    public function addMaterial(MaterialRequest $request) {
      try {
          $produit = new Produits;
          // verifier l'unicite de la reference
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
          
          return response()
            ->json('done');
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
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

  public function commandStateTest($commande) {
    try {

      $user = CommandMaterial::where('id_commande',Crypt::decryptString($commande))->first()->vendeurs()->first();
      $this->CommandChangeStatus(Crypt::decryptString($commande),$user->username);    
    // die();

      if($this->changeCommandStatusGlobale(Crypt::decryptString($commande))) {
        return response()
          ->json('done');
      };
      return response()
        ->json('fail');
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

    // TRAITEMENT DE LA REQUETE DE COMMANDE , ENVOI DU RAVITAILLEMENT
    public function makeAddStock(RavitaillementRequest $request,$commande , Produits $p ,CompenseMaterial $compMat , RapportVente $rv) {
      try {
        // existence de la commande pour le vendeur selectionne

        if($this->isExisteCommandeForVendeur(
          $request->input('vendeur'),
          $commande)) {
            
            if($this->isDisponibleInDepot($request->input('depot'),$request->input('produit'),$request->input('quantite'))) {
              // disponibilite de la quantite dans le depot central
              
              if($this->isRavitaillementPossible($commande,$request)) {

                // verifier si c'est la parabole
                $tmp = $p->where('with_serial',0)
                  ->where('reference',$request->input('produit'))->first();

                if($request->input('compense') > 0) {
                    // 
                  $compMat->quantite = $request->input('compense');
                  $compMat->commande_id = $commande;
                  $compMat->save();
                }
                
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

                // ENREGISTREMENT DE LA COMPENSE
                

                $ravitaillementVendeur->save();
                $livraison->save();
                // ENREGISTREMENT DE LA NOTIFICATION
                $n = $this->sendNotification("Ravitaillement Materiel","Ravitaillement materiel effectue au compte de :".User::where('username',$request->input('vendeur'))->first()->localisation,User::where('type','admin')->first()->username);
                $n->save();
                $n = $this->sendNotification("Ravitaillement Materiel" ,"Vous avez effectue un ravitaillement au compte de ".User::where("username",$request->input('vendeur'))->first()->localisation,Auth::user()->username);
                $n->save();
                $n = $this->sendNotification("Ravitaillement Materiel" ,"Votre commande materiel a ete confirme , rendez vous dans le depot : ".$request->input('depot'),$request->input('vendeur'));
                $n->save();
                $n = $this->sendNotification("Livraison Materiel" ,"Vous avez une livraison a effectue au compte de : ".User::where('username',$request->input('vendeur'))->first()->localisation , Depots::where("localisation",$request->input("depot"))->first()->vendeurs);
                $n->save();
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

    # FILTREZ L'INVENTAIRE DE STOCK DES VENDEURS
    
    public function filterInventoryStock($vendeurs,$status , Exemplaire $sn) {
      try {
        $serials = $sn
          ->whereNotNull('vendeurs');
          
        switch ($vendeurs) {
          case 'all':
              switch ($status) { 
                case 'all'; 
                  
                  break;
                default:
                  $serials->where('status',$status);
                  break;
              }
            break;
          default:
            switch ($status) {
              case 'all':
                $serials->where('vendeurs',$vendeurs);
              break;                
              default:
                $serials->where('vendeurs',$vendeurs)
                  ->where('status',$status);
                break;
            }
            break;
        }

        $result = $serials->orderBy('serial_number','asc')
          ->paginate(500);
        
        $all = $this->organizeInventoryData($result);


        return response()
          ->json([
            'all' =>  $all,
            'next_url'	=> $result->nextPageUrl(),
            'last_url'	=> $result->previousPageUrl(),
            'per_page'	=>	$result->perPage(),
            'current_page'	=>	$result->currentPage(),
            'first_page'	=>	$result->url(1),
            'first_item'	=>	$result->firstItem(),
            'total'	=>	$result->total()
          ]);
      }
      catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }
    }
    // ORGANISER LES DONNEES DANS UN TABLEAU

    public function organizeInventoryData($serials) {

      $all = [];
        foreach($serials as $key => $value) {
          $all[$key] = [
            'user_id' =>  $value->vendeurs,
            'numero_serie'  =>  $value->serial_number,
            'vendeurs'  => $value->vendeurs() ? $value->vendeurs()->localisation : 'undefined',
            'article' =>  $value->produit()->libelle,
            'status'  =>  $value->status,
            'origine' =>  $value->depot() ? $value->depot()->depot : ""
          ];
        }
        return $all;
    }
    
    public function getListMaterialByVendeurs(Request $request , Exemplaire $sn) {
      try {
        if($request->user()->type != 'v_da' && $request->user()->type != 'v_standart' ) {

          $serials = $sn
            ->whereNotNull('vendeurs')
            ->orderBy('serial_number','asc')
            ->paginate(500);
        }
        else {
          $serials = $sn
            ->where('vendeurs',$request->user()->username)
            ->orderBy('serial_number','asc')
            ->paginate(500);
        }

        $all = $this->organizeInventoryData($serials);
       
        return response()
          ->json([
            'all' =>  $all,
            'next_url'	=> $serials->nextPageUrl(),
            'last_url'	=> $serials->previousPageUrl(),
            'per_page'	=>	$serials->perPage(),
            'current_page'	=>	$serials->currentPage(),
            'first_page'	=>	$serials->url(1),
            'first_item'	=>	$serials->firstItem(),
            'total'	=>	$serials->total()
          ]);
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


    //
    public function makeEditMaterial(Request $request , Produits $p) {
      try {
          $validation = $request->validate([
            'reference' =>  'required|string|exists:produits,reference',
            'libelle' =>  'required|string|',
            'prix_initial'  =>  'required',
            'prix_unitaire' =>  'required',
            'quantite'  =>  'required',
            'marge' =>  'required',
            'marge_pdc' =>  'required',
            'marge_pdraf' =>  'required',
            'password_confirmation' =>  'required|string'
          ],[
            'required'  =>  '`:attribute` est requis!',
            'exists'  =>  '`:attribute` n\'existe pas dans le systeme!'
          ]);

          if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
            throw new AppException("Mot de passe invalide !");
          }
          
          $produit = $p->find($request->input('reference'));
          $produit->libelle = $request->input('libelle');
          $produit->marge = $request->input('marge');
          // $produit->prix_achat = $r
          $produit->prix_initial = $request->input('prix_initial');
          $produit->prix_vente = $request->input('prix_unitaire');
          $produit->marge_pdc = $request->input('marge_pdc');
          $produit->marge_pdraf = $request->input('marge_pdraf');

          $produit->save();
          
          return response()
            ->json('done');
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }
    }

    //

    public function getMaterialByDepot(Request $request) {
        $stock = StockPrime::select()->where('produit',$request->input('ref'))->get();
        return response()->json($stock);
    }
    //

#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


    public function getParaboleDu($commande) {

      $rappVente = RapportVente::select('id_rapport')
        ->where('vendeurs',$commande->vendeurs)
        ->where('type','migration')
        ->groupBy('id_rapport')
        ->get();

      $accessoireInfos = [];
      $terminalInfos = "";

      foreach($commande->commandProduits()->get() as $key => $value) {
        if($value->produits()->where('with_serial',0)
          ->first()) {

            array_push($accessoireInfos,
              $value->produits()
                ->where('with_serial',0)
                ->first()->reference);
          }
          else {
            $terminalInfos = $value->produits()
              ->where('with_serial',1)
              ->first()->reference;
          }
      }

      $migration = Exemplaire::whereIn('rapports',$rappVente)
        ->where('produit',$terminalInfos)
        ->count();

      // return $migration;

        $cm = CommandMaterial::select('id_commande')->where('vendeurs',$commande->vendeurs)->get();

        $compense   = CompenseMaterial::whereIn('commande_id',$cm)->sum('quantite');
          // quantite de parabole du
        $parabole_du = $migration - $compense;

        if($parabole_du > 0) {

          return $parabole_du;
        }
        return 0;

    }

    public function getRestantPourRavitaillement($commande , $materiel ,$vendeur ) {
      // Quantite de parabole restant pour ravitaillement
      #quantite commander
      $quantiteCommande = CommandProduit::where([
        'commande'  =>  $commande,
        'produit' =>  $materiel
      ])->first()->parabole_a_livrer;

      #quantite deja envoyer
      $ravitaillements  = RavitaillementVendeur::select('id_ravitaillement')->where([
        'vendeurs'  =>  $vendeur,
        'commands'  =>  $commande
      ])->get();
      
      $quantiteEnvoyer = Livraison::whereIn('ravitaillement',$ravitaillements)->where('produits',$materiel)->sum('quantite');

      $restantPourRavitaillement = $quantiteCommande - $quantiteEnvoyer;
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
      $commande = $c->find(Crypt::decryptString($slug));
      $rv = [];

      $terminal = "";
      $accessoire = [];
      
      foreach($commande->commandProduits()->get() as $key => $value) {

        if($value->produits()->first()->with_serial == 1){
          $terminal = $this->getRestantPourRavitaillement($value->commande,$value->produit,$commande->vendeurs);
        } 
        else {
          $accessoire = $this->getRestantPourRavitaillement($value->commande,$value->produit,$commande->vendeurs);
        }

      }
      
      $rv = [
        'terminal'  =>  $terminal,
        'accessoire'  => $accessoire
      ];
      $all = [
        'id'  =>  $commande->id_commande,
        'status'  =>  $commande->status,
        'vendeurs' => $commande->vendeurs,
        'vendeurs_localisation' =>  $commande->vendeurs()->first()->localisation,
        'parabole_du'  =>  $this->getParaboleDu($commande),
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

        foreach($stock as $_key => $_value) {
          $all[$key]['localisation'] = $value->localisation;

          $all[$key]['produits'][$_key] = [
            'infos' =>  $_value->produits()->first(),
            'quantite'  =>  $_value->quantite
          ];
        }

      }

      return response()
        ->json($all);

    } catch (AppException $e) {
        header("Erreur!",true,422);
        die(json_encode($e->getMessage()));
    }
  }
// RECUPERATION DES SERIALS NUMBER NON ATTRIBUE AUX VENDEURS
  public function getSerialNumberForDepot(Exemplaire $sn , Stock $s , DeficientMaterial $df) {
    try {
      $non_attribuer = $sn->select('serial_number')
        ->whereNull('vendeurs')
        ->get();

      $stock = $s
        ->whereIn('exemplaire',$non_attribuer)
        ->orderBy('exemplaire','asc')
        ->paginate(500);

        $all = [];

        foreach($stock as $key => $value) {
          $deficient = $df->where('serial_to_replace',$value->exemplaire)->first();
          $all [$key] =[
            'numero_materiel'  =>   $value->exemplaire,
            'depot' =>  $value->depot,
            'article' =>  $value->exemplaire()->produit()->libelle,
            'etat'  =>  $deficient ? 'defectueux' : '-',
            'origine' =>  $value->origine
          ];
        }
      
      return response()
        ->Json([
          'all' =>  $all,
          'next_url'	=> $stock->nextPageUrl(),
          'last_url'	=> $stock->previousPageUrl(),
          'per_page'	=>	$stock->perPage(),
          'current_page'	=>	$stock->currentPage(),
          'first_page'	=>	$stock->url(1),
          'first_item'	=>	$stock->firstItem(),
          'total'	=>	$stock->total()
        ]);
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

  // @historique de ravitaillment des depots

  public function historiqueRavitaillementDepot(Request $request , RavitaillementDepot $rd) {
    try {
        $_result = $rd->select()->orderBy('created_at','desc')->get();
        $data = [];
        foreach($_result as $key => $value) {
          $date = new Carbon($value->created_at);
          $data[$key] = [
            'date'  =>  $date->toDateString(),
            'article' =>  $value->produit()->libelle,
            'quantite'  =>  $value->quantite,
            'depot' =>  $value->depot,
            'origine' =>  $value->origine
          ];
        }
        return response()
          ->json($data);
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }
  // @ list des produits 
  public function allProduits(Produits $pr) {
    try {
        return response()->json($pr->all());
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

  // CHANGEMENT DE STATUS D'UN MATERIEL DIRECTEMENT
  public function makeActivedSerial(Request $request) {
    try {
      $validation = $request->validate([
        'serial'  =>  'required|exists:exemplaire,serial_number',
        'password_confirmation' =>  'required'
      ]);
      
      if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
        throw new AppException("Mot de passe invalide !");
      }
      
      $serial = Exemplaire::find($request->input('serial'));
      if($serial->status == 'actif') {
        throw new AppException("Materiel deja actif !");
      }
      $serial->status = 'actif';
      $serial->save();

      return response()
        ->json('done');
    }
    catch(AppException $e) {
      header("Erreur",true,422);
      die(json_encode($e->getMessage()));
    }
  }
}
