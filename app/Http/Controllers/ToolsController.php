<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeblocageCga;
use App\Mail\AnnulationDeSaisie;
use App\User;
use Illuminate\Support\Facades\Hash;


class ToolsController extends Controller
{
    //
    public function deblocageCga(Request $request) {
        $validation =   $request->validate([
            'compte_user'  =>  'required|string',
            'nom_prenom'    =>  'required|string',
            'password'  =>  'required|string',
            'num_dist'  =>  'required|string'
        ],[
            'required'  =>  'Champ(s) :attribute requis !',
            'string'    =>  'Champ(s) :attribute doit etre une chaine de caractere !'
        ]);

        try {
            //  VERIFICATION DU MOT DE PASSE
            if(!Hash::check($request->input('password'),$request->user()->password)) {
                throw new AppException("Mot de passe incorrect !");
            }

            $arrayMails = [
                'mamoudou.diallo@canal-plus.com',
                'reseautiers@loudcssyf.com',
                'michelmawuena.adjavon@canal-plus.com',
                'amadou.tall@canal-plus.com',
                'loudcssyf@hotmail.com',
                $request->user()->email
            ];

            // $arrayMails = [
            //     'bangourayans47@gmail.com'
            // ];

            // @@@@@@@@@@@@@@
                // #ceci est pour le test
            // Mail::to('layedjibacamara@gmail.com')
            //     ->cc($arrayMails)
            //     ->send(new DeblocageCga($request->user(),[
            //         'compte_user'   =>  $request->input('compte_user'),
            //         'nom_prenom'    =>  $request->input('nom_prenom'),
            //         'comment'   =>  $request->input('comment'),
            //         'num_dist'  =>  $request->input('num_dist')
            //     ]));
                    ###
                    
            Mail::to('relationdistributeur@canalplus-afrique.com')
                ->cc($arrayMails)
                ->send(new DeblocageCga($request->user(),[
                    'compte_user'   =>  $request->input('compte_user'),
                    'nom_prenom'    =>  $request->input('nom_prenom'),
                    'comment'   =>  $request->input('comment'),
                    'num_dist'  =>  $request->input('num_dist')
                ]));
            
            return response()
                ->json('done');

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getInfos(Request $request) {
        try {
            return response()
                ->json($request->user()->agence());
        } catch(AppException $e) {
            headder("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function annulationSaisi(Request $request) {
        try {
            $validation = $request->validate([
                'num_dist'  =>  'required|string',
                'num_abonne'    =>  'required|string',
                'date_saisie'   =>  'required|date',
                'password'  =>  'required',
                'saisie_errone' =>  'required|string'
            ],[
                'required'  =>  'Champ(s) :attribute requis!',
                'string'    =>  'Champ(s) :attribute doit etre une chaine de caractere!'
            ]);
            
            if(!Hash::check($request->input('password'),$request->user()->password)) {
                throw new AppException("Mot de passe incorrect !");
            }

            $arrayMails = [
                'mamoudou.diallo@canal-plus.com',
                'reseautiers@loudcssyf.com',
                'michelmawuena.adjavon@canal-plus.com',
                'amadou.tall@canal-plus.com',
                'loudcssyf@hotmail.com',
                $request->user()->email
            ];

            // $arrayMails = [
            //     'bangourayans47@gmail.com'
            // ];

            // @@@@@@@@@@@@@@
                // #ceci est pour le test
            // Mail::to('layedjibacamara@gmail.com')
            //     ->cc($arrayMails)
            //     ->send(new AnnulationDeSaisie([
            //         'num_abonne'   =>  $request->input('num_abonne'),
            //         'date_saisie'    =>  $request->input('date_saisie'),
            //         'comment'   =>  $request->input('comment'),
            //         'num_dist'  =>  $request->input('num_dist'),
            //         'saisie_correcte'   =>  $request->input('saisie_correcte'),
            //         'saisie_errone' =>  $request->input('saisie_errone')
            //     ]));
                    ###
                    
            Mail::to('relationdistributeur@canalplus-afrique.com')
                ->cc($arrayMails)
                ->send(new AnnulationDeSaisie([
                    'num_abonne'   =>  $request->input('num_abonne'),
                    'date_saisie'    =>  $request->input('date_saisie'),
                    'comment'   =>  $request->input('comment'),
                    'num_dist'  =>  $request->input('num_dist'),
                    'saisie_correcte'   =>  $request->input('saisie_correcte'),
                    'saisie_errone' =>  $request->input('saisie_errone')
                ]
            ));

            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
