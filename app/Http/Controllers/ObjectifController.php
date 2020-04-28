<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\Objectif;
use App\RapportVente;
use App\User;

class ObjectifController extends Controller
{
    //

    // recrutement classification

    protected $recrutement = [
        'class_a'   =>  [
            'name'  =>  'A',
            'min'   =>  30,
        ],
        'class_b'   =>  [
            'name'  =>  'B',
            'min'   =>  15,
            'max'   =>  30
        ],
        'class_c'   =>  [
            'name'  =>  'C',
            'min'   =>  0,
            'max'   =>  15
        ]
    ];

    // reabonnement classification

    protected $reabonnement = [
        'class_a'   =>  [
            'name'  =>  'A',
            'min'   =>  40000000,
        ],
        'class_b'   =>  [
            'name'  =>  'B',
            'min'   =>  15000000,
            'max'   =>  40000000
        ],
        'class_c'   =>  [
            'name'  =>  'C',
            'min'   =>  0,
            'max'   =>  15000000
        ]
    ];
    
    public function firstStepValidation(Request $request , Objectif $obj) {
        try {
            

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function classificationVendeurByCA(Request $request , RapportVente $rv , User $u) {
        try {

            $validation = $request->validate([
                // 'objectif_name' =>  'required|string',
                // 'debut' =>  'required|date',
                // 'fin'   =>  'required|date',
                'evaluation'    =>  'required|numeric',
                // 'marge_arriere' =>  'required|numeric'
            ],[
                'required'  =>  'Champ(s) :attribute requis',
                'numeric'   =>  'Champ(s) :attribute doit un nombre',
                'date'  =>  'Champ(s) :attribute doit etre une date'
            ]);

            $users = $u->whereIn('type',['v_da','v_standart'])->get();

            $theMonth = date('m');
            $objRapportRecrutement = [];
            $objRapportReabonnement = [] ;
            $userRapport = [];

            foreach($users as $key => $value) {

                $moyRecrutement = 0;
                $moyReabonnement = 0;

                for($i = 1 ; $i <= $request->input('evaluation') ; $i++ ) {

                    $objRapportRecrutement[$i] = $rv->whereYear('date_rapport',date('Y'))
                        ->whereMonth('date_rapport',$theMonth - $i)
                        ->where('type','recrutement')
                        ->where('vendeurs',$value->username)
                        ->sum('quantite');
                    

                    $objRapportReabonnement[$i] =  $rv->whereYear('date_rapport',date('Y'))
                        ->whereMonth('date_rapport',$theMonth - $i)
                        ->where('type','reabonnement')
                        ->where('vendeurs',$value->username)
                        ->sum('montant_ttc');
                    
                    $moyRecrutement = ($moyRecrutement + $objRapportRecrutement[$i]);
                    $moyReabonnement = ($moyReabonnement + $objRapportReabonnement[$i]);
                }

                $userRapport [$key] = [
                    // 'recrutement'   =>  $objRapportRecrutement,
                    // 'reabonnement'  =>  $objRapportReabonnement,
                    'user'  =>  $value->localisation,
                    'moyenne_recrutement'   =>  round($moyRecrutement/$request->input('evaluation')),
                    'moyenne_reabonnement'  =>  round($moyReabonnement/$request->input('evaluation')),
                    'class_recrutement' =>  '',
                    'class_reabonnement' =>  ''
                ];

            }

            // CLASSIFICATION DES VENDEURS

            #EN FONCTION DE LA MOYENNE DE RECRUTEMENT
            $date_result = [];
            foreach($userRapport as $key => $value) {
                $data_result[$key] = $this->makeClassifyForRecrutement($value);
            }

            foreach($data_result as $key => $value) {
                $data_result[$key] = $this->makeClassifyForReabonnement($value);
            }

            return response()
                ->json($data_result);

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function makeClassifyForRecrutement($value) {

        if($value['moyenne_recrutement'] <= 15) {
            $value['class_recrutement'] = $this->recrutement['class_c']['name'];
        } else if (15 < $value['moyenne_recrutement'] && $value['moyenne_recrutement'] <= 30) {
            $value['class_recrutement'] = $this->recrutement['class_b']['name'];
        } else {
            $value['class_recrutement'] = $this->recrutement['class_a']['name'];
        }

        return $value;
    }

    public function makeClassifyForReabonnement($value) {
        if($value['moyenne_reabonnement'] <= 15000000) {
            $value['class_reabonnement'] = $this->reabonnement['class_c']['name'];
        } else if(15000000 < $value['moyenne_reabonnement'] && $value['moyenne_reabonnement'] <= 40000000) {
            $value['class_reabonnement'] = $this->reabonnement['class_b']['name'];
        } else {
            $value['class_reabonnement'] = $this->reabonnement['class_a']['name'];
        }
        return $value;
    }
}
