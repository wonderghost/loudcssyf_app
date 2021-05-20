<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Exceptions\AppException;
use App\StockPrime;
use App\Depots;
use App\CommandCredit;
use App\CommandMaterial;
use App\Livraison;
use Illuminate\Support\Arr;
use App\RapportVente;
use App\Abonnement;
use Carbon\Carbon;
use App\Produits;
use Illuminate\Support\Facades\DB;


class ChartController extends Controller
{
    //
    protected $months = [
                'janvier'   =>  1,
                'fevrier'   =>  2,
                'mars'  =>  3,
                'avril' =>  4,
                'mai'   =>  5,
                'juin'  =>  6,
                'juillet'   =>  7,
                'aout'  =>  8,
                'septembre' =>  9,
                'octobre'   =>  10,
                'novembre'  =>  11,
                'decembre'  =>  12
            ]; 

    public function userStat(User $u) {
        try {
            $result = $u->select('type')->groupBy('type')->get();
            $all = [];
            foreach($result as $key =>  $value) {
                $count = $u->where('type',$value->type)->count();
                $all[$key] = [
                    'type'  =>  $value->type,
                    'count'    =>  $count,
                ];
            }
            return response()
                ->json($all);
        } catch (AppException $e) {
            header("Erreur!",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function commandStat(CommandCredit $cc) {
        try {
            $result = $cc->select('status')->groupBy('status')->get();
            $all = [];
            foreach($result as $key => $value) {
                $com = $cc->where('status',$value->status)->count();
                $all[$key] =[
                    'state' =>  $value->status,
                    'count' =>  $com
                ];
            }
            return response()
                ->json($all);
        } catch (AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function commandMaterialStat(CommandMaterial $cm) {
        try {
            $result = $cm->select('status')->groupBy('status')->get();
            $all = [];
            foreach($result as $key => $value) {
                $com = $cm->where('status',$value->status)->count();
                $all[$key] = [
                    'status'    =>  $value->status,
                    'count' =>  $com
                ];
            }

            return response()
                ->json($all);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function livraisonMaterialStat(Livraison $l) {
        try {
            $result = $l->select('status')->groupBy('status')->get();
            $data = [];
            foreach($result as $key => $value) {
                $liv = $l->where('status',$value->status)->count();
                $data[$key] = [
                    'status'    =>  $value->status,
                    'count' =>  $liv
                ];
            }
            return response()
                ->json($data);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // @@@@@@@@@@@@@@@@@@@@@@@@@ PERFOMANCE OBJECTIFS @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    public function rapportStat(RapportVente $r , $vendeur = "" , $type = "recrutement") {
        try {
            $data = [];
            $result = [];
            $i = 0;
            if($vendeur != "") {
                foreach($this->months as $key => $value) {
                    // Quantite de recrutment
                    $qt = $r->whereYear('date_rapport',date('Y'))
                                    ->whereMonth('date_rapport',$value)
                                    ->where('type',$type)
                                    ->where('vendeurs',$vendeur)
                                    ->where('state','unaborted')
                                    ->sum('quantite');
                    // Le cumule du montant TTC
                    $ttc = $r->whereYear('date_rapport',date('Y'))
                                ->whereMonth('date_rapport',$value) 
                                ->where('type',$type)
                                ->where('vendeurs',$vendeur)
                                ->where('state','unaborted')
                                ->sum('montant_ttc');
                    // cumule des commissions
                    $comission = $r->whereYear('date_rapport',date('Y'))
                                    ->whereMonth('date_rapport',$value)
                                    ->where('type',$type)
                                    ->where('vendeurs',$vendeur)
                                    ->where('state','unaborted')
                                    ->sum('commission');
                    $data[$i++] = [
                        'date' =>  $key,
                        'quantite'  =>  $qt,
                        'ttc'   =>  $ttc,
                        'commission'    =>  $comission
                    ];
                }
            }
            else {
                foreach($this->months as $key => $value) {
                    // Quantite de recrutment
                    $qt = $r->whereYear('date_rapport',date('Y'))
                                    ->whereMonth('date_rapport',$value)
                                    ->where('type',$type)
                                    ->where('state','unaborted')
                                    ->sum('quantite');
                    // Le cumule du montant TTC
                    $ttc = $r->whereYear('date_rapport',date('Y'))
                                ->whereMonth('date_rapport',$value) 
                                ->where('type',$type)
                                ->where('state','unaborted')
                                ->sum('montant_ttc');
                    // cumule des commissions
                    $comission = $r->whereYear('date_rapport',date('Y'))
                                    ->whereMonth('date_rapport',$value)
                                    ->where('type',$type)
                                    ->where('state','unaborted')
                                    ->sum('commission');
                    $data[$i++] = [
                        'date' =>  $key,
                        'quantite'  =>  $qt,
                        'ttc'   =>  $ttc,
                        'commission'    =>  $comission
                    ];
                }
            }

            return $data;
            
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
    public function getRecrutementStat(RapportVente $r) {
        try {
            return response()
                ->json($this->rapportStat(
                    $r,
                    "",
                    'recrutement'
                ));
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getMigrationStat(RapportVente $r) {
        try {
            return response()
                ->json($this->rapportStat(
                    $r,
                    '',
                    'migration'
                ));
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getReabonnementStat(RapportVente $r) {
        try {
            return response()
                ->json($this->rapportStat(
                    $r,
                    "",
                    'reabonnement'
                ));
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    public function makeFilter(Request $request , RapportVente $r) {
        try {
            $validation = $request->validate([
                'vendeurs'  =>  'required|exists:users,username',
                'du'    => 'required|date',
                'au'    =>  'required|date'
            ],[
                'required'  =>  'Champ(s) `:attribute` requis'
            ]);

           
            return response()
                ->json($this->filterPerformance(
                    $request->input('vendeurs'),
                    $request->input('du'),
                    $request->input('au'),
                    $r
                ));
        } catch(AppException $e) {
            die(json_encode($e->getMessage()));
        }
    }

    public function filterPerformance($vendeur , $du , $au , RapportVente $r) {
        try {
            
            ##
            $dataRecrutement = [];
            $dataReabonnement = [];

            $result = $r->whereDate('date_rapport','>=',$du)
                        ->whereDate("date_rapport","<=",$au)
                        ->where('type','reabonnement')
                        ->orderBy('date_rapport','asc')
                        ->where('vendeurs',$vendeur)
                        ->get();

            $_result = $r->whereDate('date_rapport','>=', $du)
                         ->whereDate('date_rapport','<=',$au)
                         ->where('type','recrutement')
                         ->orderBy('date_rapport','asc')
                         ->where('vendeurs',$vendeur)
                         ->get();

            foreach($result as $key => $value) {
                $dataReabonnement [$key] = [
                    'date'  =>  $value->date_rapport,
                    'ttc'   =>  $value->montant_ttc,
                    'commission'    =>  $value->commission
                ];
            }

            foreach($_result as $key => $value) {
                $dataRecrutement [$key] = [
                    'date'  =>  $value->date_rapport,
                    'ttc'   =>  $value->montant_ttc,
                    'quantite'  =>  $value->quantite,
                    'commission'    => $value->commission
                ];
            }
            ##
            return [
                'reabonnement'  =>  $dataReabonnement,
                'recrutement'    =>  $dataRecrutement
            ];
        } 
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    // #donnees de performances pour les vendeurs

    public function performanceVendeurRecrutement(Request $request , RapportVente $r) {
        try {
            
            return response()
                ->json($this->rapportStat(
                    $r,
                    $request->user()->username,
                    "recrutement"
                ));
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function performanceVendeurReabonnement(Request $request , RapportVente $r) {
        try {
            
            return response()
                ->json($this->rapportStat(
                    $r,
                    $request->user()->username,
                    "reabonnement"
                ));
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    public function getActeReabonnementStat() {
        try {
            $acteReabo = [];

            foreach($this->months as $value)
            {
                $acteReabo[$value] = DB::table('rapport_vente')
                    ->where('type','reabonnement')
                    ->where('state','unaborted')
                    ->whereYear('debut',date('Y'))
                    ->whereMonth('debut',$value)
                    ->join('abonnements','rapport_vente.id_rapport','=','abonnements.rapport_id')
                    ->where('upgrade',false)
                    ->get();
            }

            $statByMonth = [];

            $serials = [];

            foreach($acteReabo as $key => $value) {
                $acte = 0;
                $plus = 0;
                $duree = [];

                $serials[$key] = [
                    'data'  =>  [],
                    'month' =>  array_keys($this->months,$key)
                ];
                
                foreach($value as $_value) {

                    if($_value->duree > 1) {

                        $acte++;
                        
                        $debut = new Carbon($_value->debut);

                        array_push($duree,$_value->duree);
                        
                    }
                    else {
                        $acte++;
                    }

                    array_push($serials[$key]['data'],[
                        'serial'    =>  $_value->serial_number,
                        'debut' =>  $_value->debut,
                        'formule'   =>  $_value->formule_name,
                        'duree' =>  $_value->duree
                    ]);
                    
                }

                array_push($statByMonth,[ 
                    'date'  =>  array_keys($this->months,$key),
                    'acte_reabo'    =>  $acte,
                    'duree' =>  $duree,
                    'data_abonnement'   =>  ''
                ]);
            }

            foreach($statByMonth as $key => $value) {
                foreach($value['duree'] as $_value) {
                    $rest = $_value - 1;
                    for($k = 0 ;$k < $rest ; $k++) {
                        if(($key+$k+1) < 12) {
                            $statByMonth[$key + $k+1]['acte_reabo'] = $statByMonth[$key + $k+1]['acte_reabo'] + 1;
                        }
                    }
                }
            }

            return response()->json([
                'stats' =>  $statByMonth,
                // 'serials'   =>  $serials,
            ],200);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }


    /**
     * Exportation des actes de reabonnements
     */
    public function acteReabonnementExport()
    {
        try
        {
            $validation = request()->validate([
                'month'  =>  'required',
                'year'    =>  'required'
            ]);

            foreach($this->months as $value)
            {
                $acteReabo[$value] = DB::table('rapport_vente')
                    ->where('type','reabonnement')
                    ->where('state','unaborted')
                    ->whereYear('debut',request()->year)
                    ->whereMonth('debut',$value)
                    ->join('abonnements','rapport_vente.id_rapport','=','abonnements.rapport_id')
                    ->where('upgrade',false)
                    ->get();
            }

            $statByMonth = [];

            $serials = [];

            foreach($acteReabo as $key => $value) {
                $acte = 0;
                $plus = 0;
                $duree = [];

                $serials[$key] = [
                    'data'  =>  [],
                    'month' =>  array_keys($this->months,$key)
                ];
                
                foreach($value as $_value) {

                    if($_value->duree > 1) {

                        $acte++;
                        
                        $debut = new Carbon($_value->debut);

                        array_push($duree,$_value->duree);
                        
                    }
                    else {
                        $acte++;
                    }

                    array_push($serials[$key]['data'],[
                        'serial'    =>  $_value->serial_number,
                        'debut' =>  $_value->debut,
                        'formule'   =>  $_value->formule_name,
                        'duree' =>  $_value->duree
                    ]);
                    
                }

                array_push($statByMonth,[ 
                    'date'  =>  array_keys($this->months,$key),
                    'acte_reabo'    =>  $acte,
                    'duree' =>  $duree,
                    'data_abonnement'   =>  ''
                ]);
            }

            foreach($statByMonth as $key => $value) {
                foreach($value['duree'] as $_value) {
                    $rest = $_value - 1;
                    for($k = 0 ;$k < $rest ; $k++) {
                        if(($key+$k+1) < 12) {
                            $statByMonth[$key + $k+1]['acte_reabo'] = $statByMonth[$key + $k+1]['acte_reabo'] + 1;
                        }
                    }
                }
            }

            return response()->json($serials[request()->month],200);
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }


    /**
     * Statistiques de depots
     */
    public function depotStat()
    {
        try
        {
            $response = Depots::select('localisation')->get();
            $keys = ['localisation'];
            
            foreach(Produits::select('libelle')->get() as $value)
            {
                array_push($keys,$value->libelle);
            }

            foreach($response as $value)
            {
                foreach($value->stockMateriel()->get() as $_value)
                {
                    $value[$_value->produits()->first()->libelle] = $_value->quantite;
                }
            }
            return response()->json([
                'response'  =>  $response,
                'keys'  =>  $keys
            ],200);
        }
        catch(AppException $e)
        {
            header("Erreur",true,422);
            return response()->json($e->getMessage(),422);
        }
    }
  
}
