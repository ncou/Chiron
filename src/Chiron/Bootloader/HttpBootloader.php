<?php

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Http;
use Chiron\Http\Middleware\ErrorHandlerMiddleware;

final class HttpBootloader extends AbstractBootloader
{
    public function boot(Http $http, HttpConfig $config): void
    {
        // add the error handler middleware at the max top position in the middleware stack.
        if ($config->getHandleException() === true) {
            $http->addMiddleware(ErrorHandlerMiddleware::class, Http::MAX);
        }

        // add the middlewares with default priority (second arg in the function "middleware").
        foreach ($config->getMiddlewares() as $middleware) {
            $http->addMiddleware($middleware, Http::NORMAL);
        }
    }
}
