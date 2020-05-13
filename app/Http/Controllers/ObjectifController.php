<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Exceptions\AppException;
use App\Objectif;
use App\ObjVendeur;
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

    // 
    
    



    # DASHBOARD STATISTIQUES

    public function getDetails(Request $request , Objectif $obj) {
        try {
            $objectif = $obj->find($request->input("id_objectif"));
            $obj_vendeur = $objectif->objectifVendeurs();

            return response()
                ->json($obj_vendeur);
        } catch(AppException $e) {
            header("Erreur!",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getObjectifReabonnementStat(Request $request , Objectif $obj , ObjVendeur $obv , RapportVente $rp) {
        try {
            
            $month = date('m');
            $objectif = $obj->select()->whereYear('debut',2020)
                ->whereMonth('debut',$month)
                ->whereYear('fin',2020)
                ->whereMonth('fin',$month)
                ->first();

            if($objectif)  {

                $userObjectifClassify = [
                    'class_a'   =>  $obv->where('id_objectif',$objectif->id)->where('classe_reabonnement','A')->get(),
                    'class_b'   =>  $obv->where('id_objectif',$objectif->id)->where('classe_reabonnement','B')->get(),
                    'class_c'   =>  $obv->where('id_objectif',$objectif->id)->where('classe_reabonnement','C')->get()
                ];
                
                $dataObjectif = [];
                $i = 0 ;

                foreach($userObjectifClassify as $key   =>  $value) {
                    $som = 0;
                    $atteint = 0;
                    foreach($value as $_value) {
                        $som += $_value->plafond_reabonnement;
                        $rapp = $rp->whereYear('date_rapport',2020)
                            ->whereMonth('date_rapport',$month)
                            ->where('vendeurs',$_value->vendeurs)
                            ->where('type','reabonnement')
                            ->sum('montant_ttc');
                            
                        $atteint += $rapp;
                        $dataObjectif[$i] = [  
                            'plafond'   =>  $som,
                            'realise'   =>  $atteint,
                            'pourcent'  =>  $atteint/$som,
                            'class' =>  $_value->classe_reabonnement
                        ];
                    }
                    $i++;
                }

                return response()
                    ->json($dataObjectif);
            }

            throw new AppException("Aucune donnees pour le mois en cours ...");

        } catch(AppException $e) {
            header("Erreur!",true,422);
            die(json_encode($e->getMessage()));
        }
    }
   
    
    public function getObjectifRecrutementStat(Request $request , Objectif $obj , ObjVendeur $obv , RapportVente $rp) {
        try {
            $month = date('m');
            $objectif = $obj->select()->whereYear('debut',2020)
                ->whereMonth('debut',$month)
                ->whereYear('fin',2020)
                ->whereMonth('fin',$month)
                ->first();

            if($objectif) {

                $userObjectifClassify = [
                    'class_a'   =>  $obv->where('id_objectif',$objectif->id)->where('classe_recrutement','A')->get(),
                    'class_b'   =>  $obv->where('id_objectif',$objectif->id)->where('classe_recrutement','B')->get(),
                    'class_c'   =>  $obv->where('id_objectif',$objectif->id)->where('classe_recrutement','C')->get()
                ];
                // Calcul de la moyenne
                $dataObjectif = [];
                $i = 0;
                foreach($userObjectifClassify as $key   =>  $value) {
                    $som = 0;
                    $atteint = 0;
                    foreach($value as $_value) {
                        $som += $_value->plafond_recrutement;
                        $rapp = $rp->whereYear('date_rapport',2020)
                            ->whereMonth('date_rapport',$month)
                            ->where('vendeurs',$_value->vendeurs)
                            ->where('type','recrutement')
                            ->sum('quantite');
                            
                        $atteint += $rapp;
                        $dataObjectif[$i] = [  
                            'plafond'   =>  $som,
                            'realise'   =>  $atteint,
                            'pourcent'  =>  $atteint/$som,
                            'class' =>  $_value->classe_recrutement
                        ];
                    }
                    $i++;
                }
    
                
    
                $dataClassA = [];
                $dataClassB = [];
                $dataClassC = [];
                
    
                return response()
                    ->json($dataObjectif);
            }

            throw new AppException("Aucune Donnee pour le mois en cours!");

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    #LISTING ALL OBJECTIFS
    public function AllObjectifs(Objectif $obj , ObjVendeur $obv) {
        try {   
            $data = $obj->all();
            $_data = [];
            foreach($data as $key => $value) {

                $recrutement = [
                    'a' =>  $obv->where('id_objectif',$value->id)
                        ->where('classe_recrutement','A')
                        ->count(),
                    'b' =>  $obv->where('id_objectif',$value->id)
                        ->where('classe_recrutement','B')
                        ->count(),
                    'c' =>  $obv->where('id_objectif',$value->id)
                        ->where('classe_recrutement','C')
                        ->count()
                ];
                
                $reabonnement = [
                    'a' =>  $obv->where('id_objectif',$value->id)
                        ->where('classe_reabonnement','A')
                        ->count(),
                    'b' =>  $obv->where('id_objectif',$value->id)
                        ->where('classe_reabonnement','B')
                        ->count(),
                    'c' =>  $obv->where('id_objectif',$value->id)
                        ->where('classe_reabonnement','C')
                        ->count()
                ];

                $_data[$key] = [
                    'id'    =>  $value->id,
                    'name'  =>  $value->name,
                    'debut' =>  $value->debut,
                    'fin'   =>  $value->fin,
                    'evaluation'    =>  $value->evaluation,
                    'marge_arriere' =>  $value->marge_arriere,
                    'recrutement'   =>  $recrutement,
                    'reabonnement'  =>  $reabonnement
                ];
            }
            return response()
                ->json($_data);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
    #@@@@@@@

    public function finaliseMakeObjectif(Request $request) {
        try {

            $validation = $request->validate([
                'objectif_name' =>  'required|string',
                'debut' =>  'required|date|before:fin',
                'fin'   =>  'required|date',
                'evaluation'    =>  'required|numeric|min:3|max:12',
                'marge_arriere' =>  'required',
                'plafond_recrutement.*'   =>  'required',
                'plafond_reabonnement.*'    =>  'required'
            ],[
                'required'  =>  '`:attribute` requis',
                'numeric'   =>  '`:attribute` doit etre un nombre',
                'min'   =>  'la valeur minimal de `:attribute` est de 3'
            ]);
            
            $obj = new Objectif;
            $obj->makeId();
            $obj->name = $request->input('objectif_name');
            $obj->debut = $request->input('debut');
            $obj->fin = $request->input('fin');
            $obj->evaluation = $request->input('evaluation');
            $obj->marge_arriere = $request->input('marge_arriere');
            
            $objectif_identification = $obj->id;
            $obj->save();
            
            
            # Enregistrement de l'objectif
            
            $data = [];
            foreach($request->input('users') as $key => $value) {
                $objV = new ObjVendeur;
                $objV->id_objectif = $objectif_identification;
                $objV->vendeurs = $value['username'];
                $objV->classe_recrutement = $value['class_recrutement'];
                $objV->classe_reabonnement = $value['class_reabonnement'];

                // RECRUTEMENT 
                switch ($value['class_recrutement']) {
                    case 'A':
                        $objV->plafond_recrutement = $request->input('plafond_recrutement')['class_a'];

                    break;
                    case 'B' :
                        $objV->plafond_recrutement = $request->input('plafond_recrutement')['class_b'];
                    break;

                    case 'C' : 
                        $objV->plafond_recrutement = $request->input('plafond_recrutement')['class_c'];
                    break;
                    default:
                        throw new AppException("Erreur ! Veuillez ressayez ulterieurement ...");
                    break;
                }

                // REABONNEMENT
                
                switch ($value['class_reabonnement']) {
                    case 'A':
                        $objV->plafond_reabonnement = $request->input('plafond_reabonnement')['class_a'];
                    break;
                    case 'B':
                        $objV->plafond_reabonnement = $request->input('plafond_reabonnement')['class_b'];
                    break;
                    case 'C':
                        $objV->plafond_reabonnement = $request->input('plafond_reabonnement')['class_c'];
                    break;
                    
                    default:
                        throw new AppException("Erreur ! Veuillez ressayer ulterieurement ...");
                    break;
                }

                $objV->save();
                
            }

            return response()
                ->json('done');
                
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function classificationVendeurByCA(Request $request , RapportVente $rv , User $u) {
        try {

            $validation = $request->validate([
                'evaluation'    =>  'required|numeric',
            ],[
                'required'  =>  'Champ(s) :attribute requis',
                'numeric'   =>  'Champ(s) :attribute doit un nombre',
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
                    'username'  =>  $value->username,
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
