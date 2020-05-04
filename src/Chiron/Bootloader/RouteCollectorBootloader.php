<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Http\Config\HttpConfig;
use Chiron\Router\RouteCollector;

// TODO : on devrait pas plutot déplacer cette classe dans le package générique du Router (qui contient la classe RouteCollector en l'occurence...) ????
class RouteCollectorBootloader implements BootloaderInterface
{
    public function boot(RouteCollector $routeCollector, HttpConfig $httpConfig)
    {
        $routeCollector->setBasePath($httpConfig->getBasePath());
    }
}
