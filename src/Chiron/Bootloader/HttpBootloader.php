<?php

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Http\Config\HttpConfig;
use Chiron\Http\Http;

final class HttpBootloader extends AbstractBootloader
{
    public function boot(Http $http, HttpConfig $httpConfig): void
    {
        // add the middlewares.
        /*
        foreach ($httpConfig->getMiddlewares() as $middleware) {
            $http->addMiddlewares($middleware);
        }*/

        //$a = 10/0;
        //throw new \RuntimeException("FOOBAR !!!!!!!!!!!!!!");
        //@strpos();

        $http->addMiddlewares($httpConfig->getMiddlewares());
    }
}
