<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exemplaire;
use App\Exceptions\AppException;
use Carbon\Carbon;

class SearchController extends Controller
{
    //
    public function finSerialNumber(Request $request , Exemplaire $e) {
        try {
            $result = $e->find($request->input('dataSearch'));
            if(!$result) {
                throw new AppException("Aucune donnee trouvee!");
            }

            $abonnement =   $result->abonnements();
            $rapportByAbonn = [];
            foreach($abonnement as $key => $value) {
                $value->fin = $this->calculDateFinAbonnement(new Carbon($value->debut),$value->duree);
                $value->distributeur = $value->rapportVente()->vendeurs()->localisation;
                $value->option = $value->options();

                $rapportByAbonn[$key] = $value->rapportVente();

            }
            $all = [
                'serial'    =>  $result->serial_number,
                'etat'  =>  $result->deficientMaterial() ? 'defectueux' : '-',
                'status'    =>  $result->status,
                'vendeurs'  =>  $result->vendeurs() ? $result->vendeurs()->localisation : '',
                'origine'   =>  $result->depot() ? $result->depot()->depot : '-',
                'abonnements'   =>  $abonnement,
                'rapport_vente' =>  $result->rapport() ? $result->rapport() : null,
                'rapp'  =>  $rapportByAbonn
            ];
            
            return response()
                ->json($all); 
        } catch (AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function calculDateFinAbonnement(Carbon $date,$duree) {
        $tmp =  $date->addMonths($duree)
            ->subDay()
            ->addHours(23)
            ->addMinutes(59)
            ->addSeconds(59);
        return $tmp->toDateTimeString();
    }
}
