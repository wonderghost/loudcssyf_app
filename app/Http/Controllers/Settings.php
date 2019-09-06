<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;

class Settings extends Controller
{
    //

    public function index() {
    	return view('admin.settings');
    }

    public function indexUser() {
        return view('profile-simple-users');
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
        // $oldPassword = bcrypt($request->input('old_password'));
        if(Hash::check($request->input('old_password'),Auth::user()->password)) {
            // L'ancien est valide
            $users = User::where('username',Auth::user()->username)->update(['password'=>bcrypt($request->input('new_password'))]);
            return redirect('/user/settings/')->with('success',"Mise a jour effectuée!");
        } else {
            // L'ancien mot de passe ne correspond pas
            // renvoi d'un erreur
            return  redirect('/user/settings')->with('_errors',"Le Mot de Passe n'est pas valide!");
        }
    }
}
