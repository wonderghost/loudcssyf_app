<?php

namespace App\Http\Middleware;

use Closure;

class IsRex
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
        if($user && $user->type !== 'grex') {
            return redirect('/user');
        }
        return $next($request);
    }
}
