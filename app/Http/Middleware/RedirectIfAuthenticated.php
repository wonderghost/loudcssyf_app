<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::user();
        // dd($user);
        if($user){
            if($user->type == 'admin') {
                return redirect('/app');
            }
            // else if($user->type == 'pdc') {
            //     return redirect('/user');
            // }
            // else if($user->type == 'pdraf') {
            //     return redirect('/user');
            // }
             else {
                return redirect('/user');
            }
        } 

        return $next($request);
    }
}
