<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use \App\Exceptions\AppException;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function connexion(Request $request) {
        
        $validator = $request->validate([
            'username'  =>  'required',
            'password'  =>  'required'
        ],[
            'required'  =>  'Champ(s) :attribute requis!',
        ]);
        try {
            $credentials = $request->only('username', 'password');
            if (Auth::attempt([
                'username'  =>  request()->username,
                'password'  => request()->password,
                'type'  =>  ['v_da','v_standart','logistique','gcga','coursier','controleur','commerciale','admin','gdepot']
            ])) {
            // Authentication passed...
            return response()
                ->json('done');
            } else {
                throw new AppException("Identifiant ou Mot de passe incorrect!");
            }
        } catch (AppException $e) {
            header("Erreur",true,422);
            die(json_encode($e->getMessage()));
        }
    }
}
