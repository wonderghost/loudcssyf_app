<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Exceptions\AppException;

class Settings extends Controller
{
    //

    public function index() {
    	return view('admin.settings');
    }

    public function indexUser() {
        return view('profile-simple-users');
    }

    public function profileInfos(Request $request) {
        try {
            $user = $request->user();
            $agence = $user->agence();
            return response()
                ->json([
                    'user'  =>  $user,
                    'agence'    =>  $agence
                ]);
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }


    public function changePassword(ChangePasswordRequest $request) {
    	// $oldPassword = bcrypt($request->input('old_password'));
    	if(Hash::check($request->input('old_password'),Auth::user()->password)) {
    		// L'ancien est valide
    		$users = User::where('username',Auth::user()->username)->update(['password'=>bcrypt($request->input('new_password'))]);
    		return redirect('/admin/settings/')->with('success',"Mise a jour effectuée!");
    	} else {
    		// L'ancien mot de passe ne correspond pas
    		// renvoi d'un erreur
    		return  redirect('/admin/settings')->with('_errors',"Le Mot de Passe n'est pas valide!");
    	}
    }

    public function changePasswordUser(ChangePasswordRequest $request) {
        try {
            
            if(Hash::check($request->input('old_password'),Auth::user()->password)) {
                // L'ancien est valide
                $users = User::where('username',Auth::user()->username)
                    ->update(['password'=>bcrypt($request->input('new_password'))]);

                return response()
                    ->json('done');
            } else {
                throw new AppException("Mot de Passe");
            }
        } catch(AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }        
    }
}
