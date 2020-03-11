<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Http\Config\HttpConfig;
use Chiron\Router\RouteCollector;

class RouteCollectorBootloader implements BootloaderInterface
{
    public function boot(RouteCollector $collector, HttpConfig $httpConfig)
    {
        $collector->setBasePath($httpConfig->getBasePath());
    }
}
