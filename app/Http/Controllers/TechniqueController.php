<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Exemplaire;
use App\InterventionTechnicien;
use App\Traits\SendSms;

class TechniqueController extends Controller
{
    use SendSms;
    //

    public function getListTechnicien() {
        try {
            $techniciens = User::where('type','technicien')
                ->where('status','unblocked')
                ->orderBy('created_at','desc')
                ->get();

            return response()
                ->json($techniciens);
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }

    # SEND INSTALLATION TECHNICIEN REQUEST

    public function sendInstallationRequest() {
        try {
            $validation = request()->validate([
                'materiel'  =>  'required|exists:exemplaire,serial_number',
                'nom'   =>  'required|string',
                'prenom'    =>  'required|string',
                'adress'    =>  'required|string',
                'telephone' =>  'required|string|min:9|max:9',
                'technicien'    =>  'required|string|min:9|max:9|exists:users,username',
                'password'  =>  'required|string'
            ],[
                'exists'    =>  '`:attribute` n\'existe pas dans le systeme !',
                'required'  =>  '`:attribute` requi(s) !'
            ]);

            # PASSWROD VALIDATION

            if(!Hash::check(request()->password,request()->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            # VERIFIER SI LE MATERIEL EST INACTIF

            $serial = Exemplaire::find(request()->materiel);

            if(!is_null($serial->rapports)) {
                throw new AppException("Materiel actif !");
            }

            $intervention = new InterventionTechnicien;
            $intervention->numero_materiel = request()->materiel;
            $intervention->nom_client = request()->nom." ".request()->prenom;
            $intervention->adresse = request()->adress;
            $intervention->telephone = request()->telephone;
            $intervention->id_technicien = request()->technicien;
            $intervention->id_vendeur = request()->user()->username;
            $intervention->type = 'installation';
            $intervention->description = "Installation Nouveau Materiel !";


            ### @@@@@@@@@@@@@@@@@@@@@@@@ ENVOI DE L'SMS @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
            $userTechnicien = User::where('username',request()->technicien)
                ->first();
            $messageClient = "Installation\nClient: ".$intervention->nom_client.
                "\nTel: ".$intervention->telephone.
                "\nQuart : ".$intervention->adresse.
                "\nDIST : ".request()->user()->localisation." (".request()->user()->phone.")";

            $messageTech = "Installation\nVeuillez contacter ce Technicien \n".
                "Nom: ".$userTechnicien->nom." ".$userTechnicien->prenom.
                "\nTel: ".$userTechnicien->phone.
                "\nMerci pour la confiance ... :-)";
            ### @

            if($intervention->save()) {

                if($this->sendSmsToNumber(request()->telephone,$messageTech) && $this->sendSmsToNumber(request()->technicien,$messageClient)) {
    
                    return response()
                        ->json('done');
                }
            }
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
