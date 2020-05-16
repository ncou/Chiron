<?php

use Chiron\Http\Middleware\CheckMaintenanceMiddleware;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;

return ['bufferSize'    => 8 * 1024 *1024,
        'protocol'      => '1.1',
        'basePath'      => '/',
        'headers'       => ['Content-Type' => 'text/html; charset=UTF-8'],
        'middlewares'   => [ErrorHandlerMiddleware::class, CheckMaintenanceMiddleware::class]
    ];
