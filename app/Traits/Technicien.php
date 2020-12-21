<?php 

namespace App\Traits;

use Illuminate\Http\Request;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Afrocash;

trait Technicien {
    // 

    public function addTechnicien() {
        try {
            $validation = request()->validate([
                'phone' =>  'required|string|unique:users,username',
                'nom'   =>  'required|string',
                'prenom'    =>  'required|string',
                'date_naissance'    =>  'required|date',
                'email' =>  'required|string|unique:users,email',
                'access'    =>  'required|string'
            ],[
                'required'  =>  '`:attribute` requi(s)',
                'string'    =>  '`:attribute` doit etre un string',
                'date'  =>  '`:attribute` doit etre une date'
            ]);

            // VALIDATION DU MOT DE PASSE 
            if(!Hash::check(request()->password_confirmation,request()->user()->password)) {
                throw new AppException("Mot de passe invalide !");
            }

            $user = new User;
            $user->username = request()->phone;
            $user->nom = request()->nom;
            $user->prenom = request()->prenom;
            $user->email = request()->email;
            $user->date_naissance = request()->date_naissance;
            $user->password = bcrypt("loudcssyf");
            $user->type = request()->access;
            $user->phone = request()->phone;

            $afrocashAccount = new Afrocash;
            $afrocashAccount->generateAccountNumber();
            $afrocashAccount->vendeurs = $user->username;

            $user->save();
            $afrocashAccount->save();

            return response()
                ->json('done');
        }
        catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}