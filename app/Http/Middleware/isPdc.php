<?php

namespace App\Http\Middleware;

use Closure;

class isPdc
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
        if(($user && $user->type !== 'pdc') && ($user && $user->type !== 'pdc')) {
            return redirect('/user/pdc');
        }
        return $next($request);
    }
}
