<?php

namespace App\Http\Middleware;

use Closure;

class isPdraf
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
        if(($user && $user->type !== 'pdraf') && ($user && $user->type !== 'pdraf')) {
            return redirect('/user/pdraf');
        }
        return $next($request);
    }
}
