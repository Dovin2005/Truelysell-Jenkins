<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\secureApi;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Http\Middleware\Authenticate;
//use Illuminate\Session\Middleware\StartSession;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
       // $middleware->append(StartSession::class);
        $middleware->alias([
            'auc' => \App\Http\Middleware\aucheck::class,
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'track.device' => \App\Http\Middleware\TrackUserDevice::class,
            'permission' => \App\Http\Middleware\CheckUserPermission::class,
            'preventAdmin' => \App\Http\Middleware\PreventAdminAccess::class,
            'securityHeaders' => \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

    })->create();
