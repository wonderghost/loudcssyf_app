<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeblocageCga;
use App\User;
use Illuminate\Support\Facades\Hash;


class ToolsController extends Controller
{
    //
    public function deblocageCga(Request $request) {
        $validation =   $request->validate([
            'compte_user'  =>  'required|string',
            'nom_prenom'    =>  'required|string',
            'password'  =>  'required|string'
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

            // @@@@@@@@@@@@@@
            Mail::to('relationdistributeur@canalplus-afrique.com')
                ->cc($arrayMails)
                ->send(new DeblocageCga($request->user(),[
                    'compte_user'   =>  $request->input('compte_user'),
                    'nom_prenom'    =>  $request->input('nom_prenom'),
                    'comment'   =>  $request->input('comment')
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

            return response()
                ->json($request);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
