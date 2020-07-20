<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;
// use Illuminate\Support\Str;

use App\User;
use App\Agence;
use App\Traits\Similarity;
use App\ReseauxPdc;
use App\Traits\Afrocashes;

class PdrafController extends Controller
{
    //

    use Similarity;
    use Afrocashes;

    public function generateId() {
        do {
            $_id = "PDRAF-".mt_rand(1000,9999);
        } while($this->isExistUsername($_id));

        return $_id;
    }

    public function addNewPdraf(Request $request) {
        try {
            $validation = $request->validate([
                'email' =>  'required|email|unique:users,email',
                'phone' =>  'required|string',
                'agence'    =>  'required|string',
                'access'    =>  'required|string',
                'password_confirmation' =>  'required|string',
                'pdc'   =>  'required|string|exists:users,username'
            ],[
                'required'  =>  '`:attribute` requis !',
                'email' =>  '`:attribute` doit une adresse email valide !',
                'max'   =>  '9 caracteres maximum pour le `:attribute` !',
                'min'   =>  '9 caracteres minimum pour le `:attribute` !'
            ]);

            // confirmation du mot de passe utilisateur
            if(!Hash::check($request->input('password_confirmation'),$request->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $u = new User;
            $a = new Agence;
            $r = new ReseauxPdc;



            $u->username = $this->generateId();
            $u->email = $request->input("email");
            $u->phone = $request->input('phone');
            $u->type = $request->input('access');
            $u->localisation = $request->input('agence');
            $u->password = bcrypt("loudcssyf");

            // agence inexistante
            
            do{
                $a->reference = 'AG-'.mt_rand(1000,9999);
            }while($this->isExistAgenceRef($a->reference));

            $u->agence = $a->reference;
            $a->societe = $request->input('societe');
            $a->rccm = $request->input('rccm');
            $a->ville = $request->input('ville');
            $a->adresse = $request->input('adresse');

            $r->id_pdc = $request->input('pdc');
            $r->id_pdraf = $u->username;

            $a->save();
            $u->save();
            $this->newAccount($u->username);
            $r->save();

            
            return response()
                ->json('done');

        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
