<?php
// app/Http/Kernel.php
protected $routeMiddleware  = [
     // 现有中间件...
     'auth' => \App\Http\Middleware\Authenticate::class,
     'master' => \App\Http\Middleware\MasterMiddleware::class,
     'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
     'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
     'can' => \Illuminate\Auth\Middleware\Authorize::class,
     //'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
     'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
     'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
     'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
     'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
     

];
