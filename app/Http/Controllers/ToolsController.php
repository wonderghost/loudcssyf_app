<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Mail;
// use App\Mail\DeblocageCga;
use App\Mail\AnnulationDeSaisie;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\DeblocageCga;
use Illuminate\Support\Facades\Auth;
use App\Traits\Similarity;

class ToolsController extends Controller
{
    use Similarity;

    public function ConfirmStateDeblocage(Request $request,DeblocageCga $d) {
        try {
            $validation = $request->validate([
                'deblocage_id'  =>  'required|exists:deblocage_cga,id',
                'password'  =>  'required|string'
            ]);
            if(!Hash::check($request->input('password'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $data = $d->find($request->input('deblocage_id'));
            $data->state_done = true;
            // ENVOI DE NOTIFICATIONS
            // gestionnaire de credit
            $n = $this->sendNotification("Deblocage Cga","Vous avez debloquer un compte cga pour : ".$data->vendeurs()->localisation,$request->user()->username);
            $n->save();
            // administrateur
            $admins = User::where('type','admin')->get();
            foreach($admins as $value) {
                $n = $this->sendNotification("Deblocage Cga","Vous avez debloquer un compte cga pour : ".$data->vendeurs()->localisation,$value->username);
                $n->save();
            }

            // vendeurs
            $n = $this->sendNotification("Deblocage Cga","Mot de passe Cga reinitialise. connectez vous avec ce mot de passe par defaut : 'canal' ",$data->vendeurs);
            $n->save();
                    
            $data->save();            
            return response()
                ->json('done');
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    public function getDeblocageList(Request $request , DeblocageCga $d) {
        try {
            if($request->user()->type !== 'gcga') {
                if($request->user()->type !== 'admin') {
                    throw new AppException("not_autorize");
                }
            }

            $all = $d->select()->orderBy('created_at','desc')->get();
            $data = [];
            foreach($all as $key => $value) {
                $data[$key] =[
                    'id'    =>  $value->id,
                    'nom_prenom'    =>  $value->nom_prenom,
                    'user_account'  =>  $value->user_account,
                    'vendeurs'  =>  $value->vendeurs()->localisation,
                    'num_dist'  =>  $value->vendeurs()->agence()->num_dist,
                    'state' =>  $value->state_done
                ];
            }
            return response()
                ->json($data);
        } catch(AppException $e) {
            header("Erreur!",true,422);
            die(json_encode($e->getMessage()));
        }
    }
    // DEBLOCAGE DE COMPTE CGA

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

            $deb = new DeblocageCga;
            $deb->user_account = $request->input('compte_user');
            $deb->nom_prenom = $request->input('nom_prenom');
            $deb->vendeurs = Auth::user()->username;
            $deb->state_done = false;
            $deb->save();
            
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

    //  REQUETE D'ANNULATION DE SAISIE

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
