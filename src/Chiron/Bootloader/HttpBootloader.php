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
        /*
        foreach ($httpConfig->getMiddlewares() as $middleware) {
            $http->addMiddlewares($middleware);
        }*/


        //$a = 10/0;
        //throw new \RuntimeException("FOOBAR !!!!!!!!!!!!!!");
        //@strpos();


        $http->addMiddlewares($httpConfig->getMiddlewares());

        // TODO : C'est un test, à virer !!!!
        $http->addMiddlewares(new \Middlewares\MiddlewareOne());
    }
}
