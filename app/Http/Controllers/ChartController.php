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
    public function getRecrutementStat(RapportVente $r) {
        try {
            
            $data = [];
            $result = [];
            $i = 0;
            foreach($this->months as $key => $value) {
                $qt = $r->whereYear('date_rapport',date('Y'))
                                ->whereMonth('date_rapport',$value)
                                ->where('type','recrutement')
                                ->sum('quantite');
                $ttc = $r->whereYear('date_rapport',date('Y'))
                            ->whereMonth('date_rapport',$value) 
                            ->where('type','recrutement')
                            ->sum('montant_ttc');
                $comission = $r->whereYear('date_rapport',date('Y'))
                                ->whereMonth('date_rapport',$value)
                                ->where('type','recrutement')
                                ->sum('commission');
                $data[$i++] = [
                    'month' =>  $key,
                    'quantite'  =>  $qt,
                    'ttc'   =>  $ttc,
                    'commission'    =>  $comission
                ];
            }

            return response()
                ->json($data);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getReabonnementStat(RapportVente $r) {
        try {

            $data =[];
            $result = [];
            $i = 0;
            foreach($this->months as $key => $value) {
                $ttc = $r->whereYear('date_rapport',date('Y'))
                            ->whereMonth('date_rapport',$value)
                            ->where('type','reabonnement')
                            ->sum('montant_ttc');
                
                $comission = $r->whereYear('date_rapport',date('Y'))
                                ->whereMonth('date_rapport',$value)
                                ->where('type','reabonnement')
                                ->sum('commission');

                $data[$i++] = [
                    'month' =>  $key,
                    'ttc'   =>  $ttc,
                    'commission'    =>  $comission
                ];
            }

            return response()
                ->json($data);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
