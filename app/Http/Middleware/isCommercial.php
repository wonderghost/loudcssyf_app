<?php

namespace App\Http\Middleware;

use Closure;

class isCommercial
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
        if($user && $user->type !== 'commercial') {
            return redirect('/user');
        }
        return $next($request);
    }
}
