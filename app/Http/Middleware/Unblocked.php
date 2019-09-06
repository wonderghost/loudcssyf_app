<?php

namespace App\Http\Middleware;

use Closure;

class Unblocked
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
        if($user && $user->status !== 'unblocked') {
            return redirect('/no-permission');
        }
        return $next($request);
    }
}
