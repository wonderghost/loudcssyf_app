<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilisateurSimple extends Controller
{
    //

    public function dashboard(Request $request) {
        if($request->user()->type == 'commercial') {
            return view('admin.dashboard');
        }
    	return view('simple-users.dashboard');
    }

    public function noPermission() {
    	return view('no-permission');
    }
}
