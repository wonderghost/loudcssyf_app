<?php

namespace App\Http\Middleware;

use Closure;

class IsLogistique
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if($user && $user->type !== 'logistique') {
            return redirect('/user');
        }
        return $next($request);
    }
}
