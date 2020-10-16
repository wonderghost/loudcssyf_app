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


    public function getActeReabonnementStat(RapportVente $r , Abonnement $a) {
        try {
            $rapport_vente = $r->select('id_rapport')
                ->where('type','reabonnement')
                ->where('state','unaborted')
                ->get();

            $abonnements = $a->whereIn('rapport_id',$rapport_vente)
                ->where('upgrade',0);
                
            
            $acteReabo = [];

            foreach($this->months as $value) {
                $abonnementByMonth = $a->whereIn('rapport_id',$rapport_vente)
                    ->where('upgrade',0)
                    ->whereYear('debut',date('Y'))
                    ->whereMonth('debut',$value)
                    ->get();
                
                $acteReabo[$value] = $abonnementByMonth;
            }

            $statByMonth = [];

            foreach($acteReabo as $key => $value) {
                $acte = 0;
                $plus = 0;
                $duree = [];
                
                foreach($value as $_value) {
                    if($_value->duree > 1) {
                        $acte++;
                        $rest = $_value->duree - 1;
                        $debut = new Carbon($_value->debut);
                        $fin = $debut->addMonths($_value->duree)
                            ->subDay()
                            ->addHours(23)
                            ->addMinutes(59)
                            ->addSeconds(59);
                            
                        $plus++;

                        array_push($duree,$_value->duree);
                        
                    }
                    else {
                        $acte++;

                    }
                }

                array_push($statByMonth,[ 
                    'date'  =>  array_keys($this->months,$key),
                    'acte_reabo'    =>  $acte,
                    'line'  =>  $acte,
                    'plus_dun'  =>  $plus,
                    'duree' =>  $duree
                ]);                
            }

            // $firstStat = $statByMonth;

            // return response()
            //     ->json($statByMonth);

            foreach($statByMonth as $key => $value) {
                foreach($value['duree'] as $_value) {
                    $rest = $_value - 1;
                    for($k = 0 ;$k < $rest ; $k++) {
                        if(($key+$k+1) < 12) {

                            $statByMonth[$key + $k+1]['acte_reabo'] = $statByMonth[$key + $k+1]['acte_reabo'] + 1;
                            $statByMonth[$key + $k+1]['line'] = $statByMonth[$key + $k+1]['line'] + 1;
                        }
                    }
                }
            }

            
            return response()
                ->json($statByMonth);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
  
}
