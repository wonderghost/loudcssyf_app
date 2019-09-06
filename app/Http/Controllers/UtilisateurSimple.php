<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilisateurSimple extends Controller
{
    //

    public function dashboard() {
    	return view('simple-users.dashboard');
    }

    public function noPermission() {
    	return view('no-permission');
    }
}
