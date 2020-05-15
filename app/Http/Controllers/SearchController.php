<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exemplaire;
use App\Exceptions\AppException;

class SearchController extends Controller
{
    //
    public function finSerialNumber(Exemplaire $e , $search) {
        try {
            $result = $e->find($search);
            if(!$result) {
                throw new AppException("Aucune donnee trouvee!");
            }
            $all = [
                'serial'    =>  $result->serial_number,
                'etat'  =>  $result->deficientMaterial() ? 'defectueux' : '-',
                'status'    =>  $result->status,
                'vendeurs'  =>  $result->vendeurs() ? $result->vendeurs()->localisation : 'non attibue',
                'origine'   =>  $result->depot() ? $result->depot()->depot : '-',
                'rapport_vente' =>  $result->rapport()
            ];
            
            return response()
                ->json($all);            
        } catch (AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
