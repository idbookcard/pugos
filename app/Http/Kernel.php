<?php
// app/Http/Kernel.php
protected $routeMiddleware = [
    // ... other middlewares
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
];