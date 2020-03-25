<?php
namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Requests\DepotRequest;
use App\Http\Requests\MaterialRequest;
use App\Depots;
use App\Produits;
use App\Exemplaire;
use App\StockPrime;
use App\RavitaillementDepot;
use Carbon\Carbon;
use App\Stock;
use App\Client;
use App\Repertoire;
use App\Credit;
use App\CommandMaterial;
use App\RapportVente;
use App\Livraison;
use App\RavitaillementVendeur;
use App\CommandProduit;
use App\Exceptions\CommandStatus;
use App\Exceptions\SerialException;
use App\Exceptions\AppException;
use App\SerialNumberTemp;
use App\User;
use App\Agence;
use App\Compense;
use App\Notifications;
use App\Alert;
use App\CommandCredit;
use App\Promo;


Trait Similarity {

  // verifier l'unicite du code de livraison
  public function existCode($code) {
    $temp = Livraison::where('code_livraison',$code)->first();
    if($temp) {
      return $temp;
    }
    return false;
  }
  // verifier la disponibilite de la quantite dans le depot central
  public function isQuantiteValidInDepotCentral($produit,$quantite) {
    $temp = Produits::where('reference',$produit)
      ->where('quantite_centrale','>=',$quantite)->first();
      if($temp) {
        return $temp;
      }
      return false;
  }
  // avec ou sans serial number
  public function isWithSerialNumber($reference) {
    $temp = Produits::where('reference',$reference)->where('with_serial',1)->first();
    if($temp) {
      return $temp;
    }
    return false;
  }
  // changement de status pour la commande globalement
  public function changeCommandStatusGlobale($command) {
    $flag=0;
    $command_produit = CommandProduit::where('commande',$command)->get();
    // dd($command);
    foreach ($command_produit as $key => $value) {
      if($value->status_ravitaillement > 0) {
        $flag++;
      }
    }

    //
    // dd($flag);
    if($flag == $command_produit->count()) {
      // la commande peut etre confirmer globalement
      CommandMaterial::where('id_commande',$command)->update([
        'status'  =>  'confirmed'
      ]);
      // done
      return true;
    }
    return false;
  }
  //  Verifier si le ravitaillement est possbile
  public function isRavitaillementPossible ($commande,Request $request) {
    $commande = CommandMaterial::where([
      'id_commande'  =>  $commande
    ])->first();

    $ravitaillement = RavitaillementVendeur::select('id_ravitaillement')->where('commands',$commande->id_commande)->where('vendeurs',$request->input('vendeur'))->get();

    $livraisons = Livraison::where('produits',$request->input('produit'))->whereIn('ravitaillement',$ravitaillement)->sum('quantite');

    $comProd = CommandProduit::where([
      'commande'  =>  $commande->id_commande,
      'produit' =>  $request->input('produit')
    ])->first();
    // $livraisons
    //   dd($comProd->quantite_commande >= ($livraisons+$request->input('quantite')));
      return ($comProd->quantite_commande >= ($livraisons+$request->input('quantite')));
  }

  public function CommandChangeStatus ($commande,$vendeur) {
    $flag = $this->isCommandStatusChanged($commande,$vendeur);
    // dump($flag);
    $comProd = CommandProduit::where('commande',$commande)->get();
    // dump($comProd);
    // dd($comProd);
    foreach ($comProd as $key => $value) {
      if($flag[$value->produit]  == 0) {
        // la commande n'est pas confirmer dabord pour ce produit

      } else {
        // la commande est confirmer pour ce produit
        // echo "1";
        CommandProduit::where([
          'commande'  =>  $value->commande,
          'produit' =>  $value->produit,
        ])->update([
            'status_ravitaillement' =>  1
        ]);
      }
    }
  }
  // VERIFICATION POUR LE CHANGEMENT D'ETAT DE LA COMMANDE
  public function isCommandStatusChanged($commande,$vendeur) {
    try {
      $laCommande = CommandProduit::where('commande',$commande)->get();
      $live = [];
      $flag = [];
      foreach ($laCommande as $key => $item) {
        $rav = RavitaillementVendeur::select('id_ravitaillement')->where('commands',$commande)->where('vendeurs',$vendeur)->where('livraison','non_confirmer')->get();
        $live[$key] = Livraison::where('status','unlivred')->whereIn('ravitaillement',$rav)->where('produits',$item->produit)->sum('quantite');
        if($live[$key]) {
          // dump($item->quantite_commande);
          // dump($live[$key]->quantite);
          if($item->quantite_commande > $live[$key]) {
            // 0  => non_confirmer , 1  =>  confirmer
            $flag[$item->produit] = 0;
          } else {
            $flag[$item->produit] = 1;
          }
        } else {
          $flag[$item->produit] = 0;
        }
      }

      return $flag;
    } catch (CommandStatus $e) {
      return false;
    }
  }

	// existence du materiel dans le stock
    public function isInStock($ref,$stock) {
        // verifier si le produit a ete enregistrer au moins une fois
        $stockPrime = StockPrime::select()->where('produit',$ref)->where('depot',$stock)->first();
        if($stockPrime) {
            return $stockPrime;
        }
        return false;

    }

    // existence de la reference du  materiel dans le systeme
    public function isExisteMaterial($ref) {
        $temp = Produits::select()->where('reference',$ref)->first();
        if($temp) {
            return $temp;
        }
        return false;
    }
    // existence du libelle du materiel dans le systeme
    public function isExisteMaterialName($name) {
        $temp = Produits::select()->where('libelle',$name)->first();
        if($temp) {
            return $temp;
        }
        return false;
    }

    //ORGANISER LISTE DES MATERIELS
    public function organize($tab,$quantite,$option=true) {
        $test = [
                'reference' => $tab->reference,
                'libelle' => $tab->libelle,
                'prix_unitaire' => number_format($tab->prix_vente,0,'',' '),
                'quantite' => $quantite,
                'ht'=> number_format($tab->prix_vente/1.18,0,'.',' '),
                'tva'=> number_format($tab->prix_vente-($tab->prix_vente/1.18),0,'.',' '),
                'marge' => number_format($tab->marge,0,'',' '),
                'prix_initial' => number_format($tab->prix_initial,0,'',' ')
            ] ;
            if(!$option) {
            array_forget($test,'prix_achat');
        }
            return $test;
    }

    //ORGANISER LISTE DES SOLDES VENDEURS
    public function organizeSoldeVendeurs($tab,$agence) {
        $soldes = [
          'id_vendeur'  =>  $tab->vendeur,
          'agence'  =>  $agence->societe,
          'solde' =>  $tab->solde
            ] ;
          return $soldes;
    }
     // ORGANISAER LA LISTE DES COMMANDES
    public function organizeCommand($tab,$libelle,$option=true) {
        $date = new Carbon($tab->created_at);
        $test = [
                // 'reference' => $tab->reference,
                'libelle' => $libelle,
                'quantite' => $tab->quantite,
                'depot' => $tab->depot,
                'origine' => $tab->origine,
                'date' => $date->toFormattedDateString().' | '.$date->toTimeString()
            ] ;
            if(!$option) {
            array_forget($test,'prix_achat');
        }
            return $test;
    }

    // ORGANISER LES RAVITAILLEMENTS
    public function organizeRavitaillement($tab,$libelle,$option=true) {
        $date = new Carbon($tab->created_at);
        $test = [
                // 'reference' => $tab->reference,
                'libelle' => $libelle,
                'quantite' => $tab->quantite,
                'depot' => $tab->depot,
                'date' => $date->toFormattedDateString().' | '.$date->toTimeString()
            ] ;
            if(!$option) {
            array_forget($test,'prix_achat');
        }
            return $test;
    }

    //

    public function getItemInfo($item) {
        $infos = Produits::select()->where('reference',$item)->first();
        if($infos) {
            return $infos;
        }
        return false;
    }

    //
    public function withSerialNumber($ref) {
        $temp = Exemplaire::select()->where('produit',$ref)->first();
        if($temp) {
            return $temp;
        }
        return false;
    }

    public function findSerialNumber(Request $request) {
      try {
        // verifier si le numero de serie existe deja
        $temp = Exemplaire::where("serial_number",$request->input('ref'))->first();
        // return response()->json($temp);
        if($temp) {
          // le numero existe deja
          throw new SerialException("Numero existant !");
        }
        return response()->json('success');
      }
       catch (SerialException $e) {
        return response()->json($e->getMessage());
      }
    }
    // Validation des numeros de series lors des livraisons
    public function validateSerialNumberForLivraison(Request $request) {
      try {
        if(trim($request->input('ref')) == "") {
          throw new SerialException("Ce champs ne peut etre vide !");
        }
        // verification de l'existence dans le depot specifie
        $depot = Depots::where('vendeurs',Auth::user()->username)->first();
        $existenceState = Stock::where([
          'exemplaire'  =>  $request->input('ref'),
          'depot' =>  $depot->localisation
        ])->first();

        if($existenceState) {
          // Le numero de serie existe dans le depot
          // Verifier si le numero n'est pas deja attribuer a un vendeur
          $attributionVendeurState = Exemplaire::where([
            'serial_number' =>  $request->input('ref'),
            'status'  =>  'inactif',
            'vendeurs'  =>  NULL
          ])->first();
          if($attributionVendeurState) {
            // le numero n'est pas encore attribuer
            return response()->json('done');
          }
          else {
            throw new SerialException("Ce Numero est deja attribuer!");
          }
        }
        else {
          throw new SerialException("Ce Numero n'existe pas dans votre stock!");
        }
      } catch (SerialException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
      }

    }

    public function getSerialNumber( Request $request) {
        // recuperation de la valeur en base de donnees
        $temp = Exemplaire::select()->where('serial_number',$request->input('ref'))->first();
        // return response()->json($temp);
        if($temp) {
            // le sn existe , verifier s'il existe dans le depot choisi
            $tmp = Stock::select()->where('exemplaire',$request->input('ref'))->where('depot',session('depot'))->first();
            if($tmp) {
                // le sn existe dans le depot choisi
                if($temp->vendeurs == NULL) {
                    return response()->json('done');
                } else {
                    return response()->json('is-attribuer');
                }
            }
            else {
                // le sn n'existe pas dans le depot choisi
                return response()->json('not-in-depot');
            }
        }
        else {
            // le sn n'existe pas
            return response()->json('not-in-system');
        }
    }

public function isSNInDepot($sn,$depot) {
    $temp = Stock::select()->where('exemplaire',$sn)->where('depot',$depot)->first();
    if($temp) {
        return $temp;
    }
    return false;
}
public function isSNToUser($sn,$produit) {
    $temp = Exemplaire::select()->where('serial_number',$sn)
                                ->where('produit',$produit)
                                ->where('vendeurs',NULL)
                                ->where('status','inactif')->first();
    if($temp) {
        return $temp;
    }
    return false;
}

//  VERIFIER SI LE CLIENT EXISTE DEJA
public function isExistClient($code,$vendeur) {
     $temp = Repertoire::select()->where('client',$code)->where('vendeur',$vendeur)->first();
     if($temp) {
        return $temp;
     }
     return false;
}
// VERIFIER SI LE CLIENT EST DEJA PRESENT DANS LE SYSTEMS
public function isExistClientInSystem($code) {
    $temp = Client::select()->where('email',$code)->first();
    if($temp) {
        return $temp;
    }
    return false;
}
// VERIFIER SI LE SOLDE EST CREE

public function isExistCredit($designation) {
$temp = Credit::select()->where('designation',$designation)->first();
if($temp) {
    return $temp;
}
return false;
}

// VERIFIER SI LA COMMANDE EXISTE POUR LE VENDEUR SELECTIONEE

public function isExisteCommandeForVendeur($vendeur,$id_commande) {
    $temp = CommandMaterial::where([
        'id_commande' =>$id_commande,
        'vendeurs'=>$vendeur,
        'status'=>'unconfirmed',
    ])->first();
    if($temp) {
        return $temp;
    }
    else {
        return false;
    }
}
// VERIFIER SI LA COMMANDE EXISTE TOUT COURT

public function isExistCommandOnly($command) {
  $temp = CommandMaterial::where([
    'id_commande'=>$command,
    'status'  =>'unconfirmed'
    ])->first();
  if($temp) {
    return $temp;
  }
  return false;
}


// debit du stock
public function debitStockCentral($depot,$produit,$newQuantite) {
  StockPrime::where([
    'depot' =>  $depot,
    'produit' =>  $produit
  ])->update([
    'quantite'  =>  $newQuantite
  ]);
}
//
  public function organizeCommandList($commands) {
    $all = [];
    foreach($commands as $key => $values) {
        $vendeurs = User::where('username',$values->vendeurs)->first();

        $date = new Carbon($values->created_at);
        $date->setLocale('fr_FR');
        $command_produit = CommandProduit::where([
        'commande'  =>  $values->id_commande
        ])->first();

        $migration = RapportVente::where('vendeurs',$values->vendeurs)->where('type','migration')->sum('quantite');
        $compense = Compense::where([
        'vendeurs'	=>	Auth::user()->username,
        'materiel'	=>	Produits::where('libelle','Parabole')->first()->reference
        ])->sum('quantite');

        $parabole_a_livrer  = $command_produit->parabole_a_livrer - ($migration + $compense);
        $_promo = $values->promos_id;
        $all [$key] = [
          'date'  =>  $date->toFormattedDateString().' | '.$date->toTimeString(),
          'vendeurs'  =>  $vendeurs->agence()->societe."_".$vendeurs->localisation,
          'item' => 'Kit complet',
          'quantite' => $command_produit->quantite_commande,
          'parabole_a_livrer' =>  $parabole_a_livrer,
          'status' =>  $values->status,
          'promo' =>  $_promo ? 'En Promo' : 'Hors Promo',
          'id' => $values->id,
          'link'  =>  url('user/ravitailler',[$values->id_commande])
        ];
    }
    return $all ;
  }

  public function isDisponibleInDepot($depot,$produit,$quantite) {
    $temp = StockPrime::where([
      'depot' =>  $depot,
      'produit'  =>  $produit
    ])->first();
    if($temp) {
      if($temp->quantite >= $quantite) {
        return true;
      }
      return false;
    }
    return false;
  }


  // ENVOI DE LA NOTIFICATION
  public function sendNotification($title,$description , $vendeurs) {
    // ENVOI DE LA Notifications
    $notification = new Notifications;
    $notification->makeId();
    $notification->titre = $title;
    $notification->description = $description;
    $notification->vendeurs = $vendeurs;
    // ENREGISTREMENT DE LA NOTIFICATION
    // $notification->save();
    return $notification;
  }

   // VERIFIER SI UNE PROMO N'EST PAS DEJA ACTIVE
   public function isExistPromo() {
    $temp = Promo::where('status_promo','actif')->first();
    if($temp) {
      return $temp;
    }
    return false;
  }

  // /// recuperation de la promo active
  public function getPromo(Request $request) {
    try {
      $temp = $this->isExistPromo();
      if($temp) {
        $all = [];
        $debut = new Carbon($temp->debut);
        $fin = new Carbon($temp->fin);
        $debut->setLocale('fr_FR');
        $fin->setLocale('fr_FR');
        $all = [
          'id'  =>  $temp->id,
          'intitule'  =>  $temp->intitule,
          'debut' =>  $temp->debut,
          'fin' =>  $temp->fin,
          'subvention'  =>  $temp->subvention,
          'prix_materiel' =>  $temp->prix_vente,
          'description' =>  $temp->description
        ];

        return response()->json($all);
      }
      else {
        return response()->json('fail');
      }
    } catch(AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
    }
  }

}
