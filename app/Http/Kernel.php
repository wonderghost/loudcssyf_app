<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\isAdmin::class,
        \App\Http\Middleware\Unblocked::class,
        \App\Http\Middleware\IsLogistique::class,
        \App\Http\Middleware\isCga::class,
        \App\Http\Middleware\IsRex::class,
        \App\Http\Middleware\isVendeur::class,
        \App\Http\Middleware\IsDepot::class,
        \App\Http\Middleware\isControleur::class,
        \App\Http\Middleware\isCoursier::class,
        \App\Http\Middleware\isCommercial::class,
        \App\Http\Middleware\isPdc::class,
        \App\Http\Middleware\isPdraf::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'admin' => \App\Http\Middleware\isAdmin::class,
        'unblocked' => \App\Http\Middleware\Unblocked::class,
        'logistique' => \App\Http\Middleware\IsLogistique::class,
        'cga' => \App\Http\Middleware\isCga::class,
        'rex' => \App\Http\Middleware\IsRex::class,
        'vendeur' => \App\Http\Middleware\isVendeur::class,
        'depot' =>  \App\Http\Middleware\IsDepot::class,
        'controleur' =>  \App\Http\Middleware\isControleur::class,
        'coursier' =>  \App\Http\Middleware\isCoursier::class,
        'commercial'    =>  \App\Http\Middleware\isCommercial::class,
        'pdc'   =>  \App\Http\Middleware\isPdc::class,
        'pdraf' =>  \App\Http\Middleware\isPdraf::class
    ];
}
