<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Exceptions\AppException;
use App\StockPrime;
use App\Depots;
use App\CommandCredit;

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
}
