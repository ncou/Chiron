<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Http;

class HttpBootloader implements BootloaderInterface
{
    public function boot(Http $http, HttpConfig $httpConfig)
    {
        // add the middlewares.
        foreach ($httpConfig->getMiddlewares() as $middleware) {
            $http->middleware($middleware);
        }
    }
}
