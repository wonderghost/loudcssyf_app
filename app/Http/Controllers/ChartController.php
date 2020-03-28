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

class ChartController extends Controller
{
    //

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
}
